<?php
namespace App\Services;

class ImageProcessingService {
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
        
        // Check if GD extension is loaded
        if (!extension_loaded('gd')) {
            throw new \Exception('GD library is not installed');
        }
    }
    
    public function processImage($imagePath) {
        try {
            // Get image info
            $imageInfo = getimagesize($imagePath);
            if ($imageInfo === false) {
                throw new \Exception('Invalid image file');
            }
            
            $mimeType = $imageInfo['mime'];
            
            // Create image resource using proper namespace for GD functions
            switch ($mimeType) {
                case 'image/jpeg':
                    $image = \imagecreatefromjpeg($imagePath);
                    break;
                case 'image/png':
                    $image = \imagecreatefrompng($imagePath);
                    break;
                case 'image/gif':
                    $image = \imagecreatefromgif($imagePath);
                    break;
                default:
                    throw new \Exception('Unsupported image type: ' . $mimeType);
            }
            
            if (!$image) {
                throw new \Exception('Failed to create image resource');
            }
            
            // Get original dimensions
            $width = \imagesx($image);
            $height = \imagesy($image);
            
            // Create optimized version
            $optimizedImage = \imagecreatetruecolor($width, $height);
            
            // Preserve transparency for PNG images
            if ($mimeType === 'image/png') {
                \imagealphablending($optimizedImage, false);
                \imagesavealpha($optimizedImage, true);
            }
            
            // Copy and resize image
            \imagecopyresampled(
                $optimizedImage, 
                $image, 
                0, 0, 0, 0, 
                $width, $height, 
                $width, $height
            );
            
            // Generate optimized filename
            $optimizedPath = $this->config['upload_dir'] . 'optimized_' . basename($imagePath);
            
            // Save optimized version
            switch ($mimeType) {
                case 'image/jpeg':
                    \imagejpeg($optimizedImage, $optimizedPath, 85);
                    break;
                case 'image/png':
                    \imagepng($optimizedImage, $optimizedPath, 6);
                    break;
                case 'image/gif':
                    \imagegif($optimizedImage, $optimizedPath);
                    break;
            }
            
            // Clean up
            \imagedestroy($image);
            \imagedestroy($optimizedImage);
            
            return $optimizedPath;
            
        } catch (\Exception $e) {
            throw new \Exception('Image processing failed: ' . $e->getMessage());
        }
    }
} 