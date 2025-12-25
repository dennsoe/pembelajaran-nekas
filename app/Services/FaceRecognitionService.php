<?php

namespace App\Services;

use App\Models\AttendanceSetting;
use Illuminate\Support\Facades\Storage;

class FaceRecognitionService
{
    /**
     * Store face photo
     */
    public function storeFacePhoto(string $base64Image, string $userId): string
    {
        // Remove data:image header if present
        if (str_contains($base64Image, 'base64,')) {
            $base64Image = explode('base64,', $base64Image)[1];
        }

        $imageData = base64_decode($base64Image);
        $fileName = 'faces/' . $userId . '_' . time() . '.jpg';
        
        Storage::disk('public')->put($fileName, $imageData);

        return $fileName;
    }

    /**
     * Validate face match score
     */
    public function validateFaceMatch(float $matchScore): array
    {
        $settings = AttendanceSetting::first();
        $threshold = $settings->face_match_threshold ?? 0.6;

        if ($matchScore < $threshold) {
            return [
                'valid' => false,
                'message' => "Kecocokan wajah terlalu rendah ({$matchScore}, minimal: {$threshold})",
                'score' => $matchScore,
                'threshold' => $threshold,
            ];
        }

        return [
            'valid' => true,
            'message' => 'Wajah terverifikasi',
            'score' => $matchScore,
            'threshold' => $threshold,
        ];
    }

    /**
     * Process face encoding (placeholder - implementasi sebenarnya di frontend)
     */
    public function processFaceEncoding(string $base64Image): ?string
    {
        // This is a placeholder. 
        // Real implementation will be done in frontend using face-api.js
        // and encoding will be sent to backend
        return null;
    }

    /**
     * Store face encoding untuk user profile
     */
    public function storeFaceEncoding(string $userId, string $encoding): bool
    {
        // Implementation depends on how encoding is stored
        // Could be JSON in database or separate file storage
        return true;
    }

    /**
     * Compare face encodings (placeholder)
     */
    public function compareFaces(string $encoding1, string $encoding2): float
    {
        // This is a placeholder.
        // Real comparison will be done in frontend using face-api.js
        // Backend will receive the match score
        return 0.0;
    }

    /**
     * Delete face photo
     */
    public function deleteFacePhoto(string $filePath): bool
    {
        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->delete($filePath);
        }
        return false;
    }

    /**
     * Get face photo URL
     */
    public function getFacePhotoUrl(string $filePath): string
    {
        return Storage::disk('public')->url($filePath);
    }
}
