<?php

namespace App\Services\Interfaces;

use Illuminate\Http\UploadedFile;

interface IStorageService
{
    /**
     * Upload an image file to Google Cloud Storage
     *
     * @param UploadedFile $file
     * @param string $category
     * @param string|null $customName
     * @return array
     */
    public function uploadImage(UploadedFile $file, string $category, ?string $customName = null): array;

    /**
     * Upload a recording file to Google Cloud Storage
     *
     * @param UploadedFile $file
     * @param string $category
     * @param string|null $customName
     * @return array
     */
    public function uploadRecording(UploadedFile $file, string $category, ?string $customName = null): array;

    /**
     * Download a file from Google Cloud Storage
     *
     * @param string $filePath
     * @return array
     */
    public function downloadFile(string $filePath): array;

    /**
     * Get a signed URL for a file
     *
     * @param string $filePath
     * @param int $expirationMinutes
     * @return string
     */
    public function getSignedUrl(string $filePath, int $expirationMinutes = 60): string;

    /**
     * Delete a file from Google Cloud Storage
     *
     * @param string $filePath
     * @return bool
     */
    public function deleteFile(string $filePath): bool;

    /**
     * Check if a file exists in Google Cloud Storage
     *
     * @param string $filePath
     * @return bool
     */
    public function fileExists(string $filePath): bool;

    /**
     * Get file metadata
     *
     * @param string $filePath
     * @return array|null
     */
    public function getFileMetadata(string $filePath): ?array;
}
