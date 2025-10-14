<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\IStorageService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class StorageController extends Controller
{
    private IStorageService $storageService;

    public function __construct(IStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Change user photo - upload image and update user profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changeUserPhoto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|image|max:10240', // 10MB max
            'user_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = $request->input('user_id');
            $file = $request->file('file');
            
            // Get user and check for existing photo
            $user = User::findOrFail($userId);
            $oldPhotoPath = $user->photo_path;
            
            // Generate custom filename
            $customName = "user-{$userId}-" . time();
            
            // Upload new image to storage
            $result = $this->storageService->uploadImage(
                $file,
                'profile',
                $customName
            );

            if (!$result['success'] || !$result['data']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Upload failed',
                    'data' => null
                ], 400);
            }

            // Delete old photo if exists
            if ($oldPhotoPath) {
                try {
                    $this->storageService->deleteFile($oldPhotoPath);
                    Log::info('Old user photo deleted', [
                        'user_id' => $userId,
                        'old_photo_path' => $oldPhotoPath
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't fail the upload
                    Log::warning('Failed to delete old user photo', [
                        'user_id' => $userId,
                        'old_photo_path' => $oldPhotoPath,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Update user photo_path with new file path
            $user->update(['photo_path' => $result['data']['file_path']]);

            Log::info('User photo updated', [
                'user_id' => $userId,
                'old_photo_path' => $oldPhotoPath,
                'new_file_path' => $result['data']['file_path'],
                'signed_url' => $result['data']['url']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Photo updated successfully',
                'data' => [
                    'file_path' => $result['data']['file_path'],
                    'photo_url' => $result['data']['url'], // Signed URL for immediate use
                    'user_updated' => true,
                    'old_photo_deleted' => $oldPhotoPath ? true : false
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('User photo upload failed', [
                'user_id' => $request->input('user_id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

}
