<?php

namespace App\Services;

use App\Services\Interfaces\IStorageService;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\Bucket;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StorageService implements IStorageService
{
    private StorageClient $storageClient;
    private Bucket $bucket;
    private string $bucketName;

    public function __construct()
    {
        $this->bucketName = config('filesystems.disks.gcs.bucket');
        $this->initializeStorageClient();
        $this->bucket = $this->storageClient->bucket($this->bucketName);
    }

    private function initializeStorageClient(): void
    {
        $projectId = config('filesystems.disks.gcs.project_id');
        $clientEmail = config('filesystems.disks.gcs.client_email');
        $privateKey = config('filesystems.disks.gcs.private_key');

        if (empty($projectId) || empty($clientEmail) || empty($privateKey)) {
            throw new \Exception("Google Cloud credentials not properly configured. Please check your .env file.");
        }

        $this->storageClient = new StorageClient([
            'projectId' => $projectId,
            'keyFile' => [
                'type' => 'service_account',
                'project_id' => $projectId,
                'client_email' => $clientEmail,
                'private_key' => $privateKey,
            ],
        ]);
    }

    public function uploadImage(UploadedFile $file, string $category, ?string $customName = null): array
    {
        $this->validateImageFile($file);

        $fileName = $this->generateFileName($file, $customName);
        $filePath = "images/{$category}/{$fileName}";

        return $this->uploadFile($file, $filePath);
    }

    public function uploadRecording(UploadedFile $file, string $category, ?string $customName = null): array
    {
        $this->validateRecordingFile($file);

        $fileName = $this->generateFileName($file, $customName);
        $filePath = "recordings/{$category}/{$fileName}";

        return $this->uploadFile($file, $filePath);
    }

    public function downloadFile(string $filePath): array
    {
        try {
            $object = $this->bucket->object($filePath);

            if (!$object->exists()) {
                return [
                    'success' => false,
                    'message' => 'File not found',
                    'data' => null
                ];
            }

            $content = $object->downloadAsString();
            $metadata = $object->info();

            return [
                'success' => true,
                'message' => 'File downloaded successfully',
                'data' => [
                    'content' => base64_encode($content),
                    'metadata' => $metadata,
                    'size' => strlen($content),
                    'content_type' => $metadata['contentType'] ?? 'application/octet-stream'
                ]
            ];
        } catch (\Exception $e) {
            Log::error('File download failed', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to download file: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function getSignedUrl(string $filePath, int $expirationMinutes = 60): string
    {
        try {
            $filePath = str_replace('https://storage.googleapis.com/echurchstorage/', '', $filePath);
            $object = $this->bucket->object($filePath);

            if (!$object->exists()) {
                throw new \Exception('File not found');
            }

            return $object->signedUrl(new \DateTime('+' . $expirationMinutes . ' minutes'));
        } catch (\Exception $e) {
            Log::error('Failed to generate signed URL', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function deleteFile(string $filePath): bool
    {
        try {
            $object = $this->bucket->object($filePath);

            if (!$object->exists()) {
                return false;
            }

            $object->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('File deletion failed', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    public function fileExists(string $filePath): bool
    {
        try {
            $object = $this->bucket->object($filePath);
            return $object->exists();
        } catch (\Exception $e) {
            Log::error('File existence check failed', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    public function getFileMetadata(string $filePath): ?array
    {
        try {
            $object = $this->bucket->object($filePath);

            if (!$object->exists()) {
                return null;
            }

            return $object->info();
        } catch (\Exception $e) {
            Log::error('Failed to get file metadata', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    private function uploadFile(UploadedFile $file, string $filePath): array
    {
        try {
            $fileContent = file_get_contents($file->getPathname());
            $mimeType = $file->getMimeType();

            $object = $this->bucket->upload($fileContent, [
                'name' => $filePath,
                'metadata' => [
                    'originalName' => $file->getClientOriginalName(),
                    'uploadedAt' => now()->toISOString(),
                    'uploadedBy' => auth()->id() ?? 'system'
                ]
            ]);

            // Set the content type
            $object->update(['contentType' => $mimeType]);

            return [
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => [
                    'file_path' => $filePath,
                    'file_name' => basename($filePath),
                    'file_size' => $file->getSize(),
                    'mime_type' => $mimeType,
                    'url' => $this->getSignedUrl($filePath),
                    'metadata' => $object->info()
                ]
            ];
        } catch (\Exception $e) {
            Log::error('File upload failed', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to upload file: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    private function generateFileName(UploadedFile $file, ?string $customName = null): string
    {
        if ($customName) {
            return $customName . '.' . $file->getClientOriginalExtension();
        }

        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $randomString = Str::random(8);

        return "{$timestamp}_{$randomString}.{$extension}";
    }

    private function validateImageFile(UploadedFile $file): void
    {
        $allowedMimeTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp',
            'image/gif'
        ];

        $maxSize = 10 * 1024 * 1024; // 10MB

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            throw new \InvalidArgumentException('Invalid image file type. Allowed types: jpg, jpeg, png, webp, gif');
        }

        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException('Image file too large. Maximum size: 10MB');
        }
    }

    private function validateRecordingFile(UploadedFile $file): void
    {
        $allowedMimeTypes = [
            'audio/mpeg',
            'audio/mp3',
            'audio/wav',
            'audio/mp4',
            'video/mp4',
            'video/quicktime',
            'audio/ogg'
        ];

        $maxSize = 100 * 1024 * 1024; // 100MB

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            throw new \InvalidArgumentException('Invalid recording file type. Allowed types: mp3, wav, mp4, ogg');
        }

        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException('Recording file too large. Maximum size: 100MB');
        }
    }
}
