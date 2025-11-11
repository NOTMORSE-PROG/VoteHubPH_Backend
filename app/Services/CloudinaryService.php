<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

class CloudinaryService
{
    private Cloudinary $cloudinary;
    private const FOLDER_PREFIX = 'votehubph';

    public function __construct()
    {
        Configuration::instance([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'url' => [
                'secure' => true,
            ],
        ]);

        $this->cloudinary = new Cloudinary();
    }

    /**
     * Upload an image to Cloudinary with organized folder structure
     *
     * @param string $filePath Path to the file or base64 data
     * @param string $folder Subfolder within votehubph (e.g., 'posts', 'profiles')
     * @param array $options Additional Cloudinary options
     * @return array Upload result with url and public_id
     */
    public function uploadImage(string $filePath, string $folder = 'general', array $options = []): array
    {
        $defaultOptions = [
            'folder' => self::FOLDER_PREFIX . '/' . $folder,
            'resource_type' => 'image',
            'transformation' => [
                ['width' => 1200, 'height' => 1200, 'crop' => 'limit'],
                ['quality' => 'auto'],
                ['fetch_format' => 'auto'],
            ],
        ];

        $uploadOptions = array_merge($defaultOptions, $options);

        try {
            $result = $this->cloudinary->uploadApi()->upload($filePath, $uploadOptions);

            return [
                'success' => true,
                'url' => $result['secure_url'],
                'public_id' => $result['public_id'],
                'width' => $result['width'],
                'height' => $result['height'],
                'format' => $result['format'],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete an image from Cloudinary
     *
     * @param string $publicId The public ID of the image
     * @return array
     */
    public function deleteImage(string $publicId): array
    {
        try {
            $result = $this->cloudinary->uploadApi()->destroy($publicId);

            return [
                'success' => $result['result'] === 'ok',
                'result' => $result['result'],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get optimized image URL
     *
     * @param string $publicId
     * @param int $width
     * @param int $height
     * @return string
     */
    public function getOptimizedUrl(string $publicId, int $width = 400, int $height = 400): string
    {
        return $this->cloudinary->image($publicId)
            ->resize(Resize::fill($width, $height))
            ->delivery(Quality::auto())
            ->delivery(Format::auto())
            ->toUrl();
    }

    /**
     * Upload multiple images
     *
     * @param array $files Array of file paths
     * @param string $folder
     * @return array Array of upload results
     */
    public function uploadMultipleImages(array $files, string $folder = 'general'): array
    {
        $results = [];

        foreach ($files as $file) {
            $results[] = $this->uploadImage($file, $folder);
        }

        return $results;
    }
}
