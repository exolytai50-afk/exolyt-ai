<?php
/**
 * Image Processing Service
 */

namespace App\Services;

class ImageService {
    private $uploadDir;
    private $allowedFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $maxFileSize = 5242880; // 5MB

    public function __construct() {
        $this->uploadDir = __DIR__ . '/../../public/uploads/';
        
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function uploadImage($file, $category = 'recipes') {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('File upload error');
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new \Exception('File size exceeds maximum limit');
        }

        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExt, $this->allowedFormats)) {
            throw new \Exception('File format not allowed');
        }

        $categoryDir = $this->uploadDir . $category . '/';
        if (!is_dir($categoryDir)) {
            mkdir($categoryDir, 0755, true);
        }

        $fileName = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $fileExt;
        $filePath = $categoryDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new \Exception('Failed to move uploaded file');
        }

        // Resize and optimize image
        $this->optimizeImage($filePath, $fileExt);

        return '/uploads/' . $category . '/' . $fileName;
    }

    private function optimizeImage($filePath, $ext) {
        // This would use a library like ImageMagick or GD
        // For now, basic validation
        if (!file_exists($filePath)) {
            throw new \Exception('Image file not found');
        }

        // You would implement actual image optimization here
        // For example, using GD or ImageMagick
    }

    public function deleteImage($imagePath) {
        $fullPath = __DIR__ . '/../../public' . $imagePath;

        if (file_exists($fullPath)) {
            unlink($fullPath);
            return true;
        }

        return false;
    }

    public function createThumbnail($imagePath, $width = 150, $height = 150) {
        $fullPath = __DIR__ . '/../../public' . $imagePath;

        if (!file_exists($fullPath)) {
            throw new \Exception('Image file not found');
        }

        $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        
        // Using GD library for image manipulation
        if (function_exists('imagecreatefromjpeg')) {
            switch ($ext) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($fullPath);
                    break;
                case 'png':
                    $image = imagecreatefrompng($fullPath);
                    break;
                case 'gif':
                    $image = imagecreatefromgif($fullPath);
                    break;
                default:
                    throw new \Exception('Unsupported image format');
            }

            $origWidth = imagesx($image);
            $origHeight = imagesy($image);

            // Calculate aspect ratio
            $ratio = min($width / $origWidth, $height / $origHeight);

            $newWidth = $origWidth * $ratio;
            $newHeight = $origHeight * $ratio;

            $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, 
                              $newWidth, $newHeight, $origWidth, $origHeight);

            $thumbPath = str_replace('.' . $ext, '_thumb.' . $ext, $fullPath);

            switch ($ext) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($thumbnail, $thumbPath, 90);
                    break;
                case 'png':
                    imagepng($thumbnail, $thumbPath);
                    break;
            }

            imagedestroy($image);
            imagedestroy($thumbnail);

            return str_replace('.' . $ext, '_thumb.' . $ext, $imagePath);
        }

        return $imagePath;
    }
}
