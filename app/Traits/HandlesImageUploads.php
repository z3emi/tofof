<?php

namespace App\Traits;

use Throwable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesImageUploads
{
    /**
     * Upload and convert an image to WebP.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string $disk
     * @param int $quality
     * @return string|null
     */
    protected function uploadAndConvertImage(UploadedFile $file, string $directory, string $disk = 'public', int $quality = 80): ?string
    {
        if (!$file->isValid()) {
            return null;
        }

        // If GD/WebP support is unavailable, store original format to avoid 500 errors.
        if (!extension_loaded('gd') || !function_exists('imagewebp')) {
            return $file->storeAs($directory, $file->hashName(), $disk);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = Str::slug($filename) . '-' . time() . '.webp';
        $path = rtrim($directory, '/') . '/' . $newFilename;

        try {
            // Create image resource based on extension
            $image = null;
            switch ($extension) {
                case 'jpeg':
                case 'jpg':
                    $image = @imagecreatefromjpeg($file->getRealPath());
                    break;
                case 'png':
                    $image = @imagecreatefrompng($file->getRealPath());
                    if ($image) {
                        imagealphablending($image, false);
                        imagesavealpha($image, true);
                    }
                    break;
                case 'gif':
                    $image = @imagecreatefromgif($file->getRealPath());
                    break;
                case 'webp':
                    $image = @imagecreatefromwebp($file->getRealPath());
                    break;
                default:
                    // Handle as normal file if not a standard image for conversion
                    return $file->storeAs($directory, $file->hashName(), $disk);
            }

            if (!$image) {
                // Fallback to normal upload if GD fails
                return $file->storeAs($directory, $file->hashName(), $disk);
            }

            // Output to WebP
            ob_start();
            $encoded = imagewebp($image, null, $quality);
            $webpData = ob_get_contents();
            ob_end_clean();

            imagedestroy($image);

            if (!$encoded || $webpData === false || $webpData === '') {
                return $file->storeAs($directory, $file->hashName(), $disk);
            }

            // Save to disk
            Storage::disk($disk)->put($path, $webpData);

            return $path;
        } catch (Throwable $e) {
            return $file->storeAs($directory, $file->hashName(), $disk);
        }
    }
}
