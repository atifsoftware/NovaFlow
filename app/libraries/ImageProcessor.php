<?php

namespace NovaFlow\Core;

/**
 * Image Processor Library
 * Handles image file management, resizing, and mandatory WebP conversion.
 */
class ImageProcessor
{
    protected $file;
    protected $image;
    protected $width;
    protected $height;
    protected $type;
    protected $quality = 80;

    /**
     * @param array|string $file $_FILES element or existing file path
     */
    public function __construct($file = null)
    {
        if ($file) {
            $this->load($file);
        }
    }

    /**
     * Load image from file or path
     */
    public function load($file)
    {
        if (is_array($file)) {
            $this->file = $file;
            $path = $file['tmp_name'];
            $this->type = $file['type'];
        } else {
            $path = $file;
            if (!file_exists($path)) return $this;
            $info = getimagesize($path);
            $this->type = $info['mime'];
        }

        if (!@file_exists($path)) {
            return $this;
        }

        switch ($this->type) {
            case 'image/jpeg':
            case 'image/jpg':
                $this->image = @imagecreatefromjpeg($path);
                break;
            case 'image/png':
                $this->image = @imagecreatefrompng($path);
                if ($this->image) {
                    imagepalettetotruecolor($this->image);
                    imagealphablending($this->image, true);
                    imagesavealpha($this->image, true);
                }
                break;
            case 'image/gif':
                $this->image = @imagecreatefromgif($path);
                if ($this->image) {
                    imagepalettetotruecolor($this->image);
                }
                break;
            case 'image/webp':
                $this->image = @imagecreatefromwebp($path);
                break;
        }

        if ($this->image) {
            $this->width = imagesx($this->image);
            $this->height = imagesy($this->image);
        }

        return $this;
    }

    /**
     * Resize image while maintaining aspect ratio
     */
    public function resize($maxWidth, $maxHeight)
    {
        if (!$this->image) return $this;

        $ratio = min($maxWidth / $this->width, $maxHeight / $this->height);
        
        // Don't upscale if original is smaller
        if ($ratio >= 1) return $this;

        $newWidth = (int)($this->width * $ratio);
        $newHeight = (int)($this->height * $ratio);

        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Handle transparency for WebP/PNG
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        
        imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $this->width, $this->height);
        
        imagedestroy($this->image);
        $this->image = $newImage;
        $this->width = $newWidth;
        $this->height = $newHeight;

        return $this;
    }

    /**
     * Crop image from center
     */
    public function cropCenter($width, $height)
    {
        if (!$this->image) return $this;

        $originalAspectRatio = $this->width / $this->height;
        $targetAspectRatio = $width / $height;

        if ($originalAspectRatio > $targetAspectRatio) {
            $srcHeight = $this->height;
            $srcWidth = (int)($this->height * $targetAspectRatio);
            $srcX = (int)(($this->width - $srcWidth) / 2);
            $srcY = 0;
        } else {
            $srcWidth = $this->width;
            $srcHeight = (int)($this->width / $targetAspectRatio);
            $srcX = 0;
            $srcY = (int)(($this->height - $srcHeight) / 2);
        }

        $newImage = imagecreatetruecolor($width, $height);
        
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        
        imagecopyresampled($newImage, $this->image, 0, 0, $srcX, $srcY, $width, $height, $srcWidth, $srcHeight);
        
        imagedestroy($this->image);
        $this->image = $newImage;
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    /**
     * Set WebP quality (1-100)
     */
    public function quality($quality)
    {
        $this->quality = $quality;
        return $this;
    }

    /**
     * Save current image state to destination as WebP
     * 
     * @param string $subfolder Subfolder in public/uploads/
     * @param string|null $filename Custom filename if provided
     * @return string|bool Relative path for DB if successful, false otherwise
     */
    public function saveAsWebp($subfolder = 'products', $filename = null)
    {
        if (!$this->image) return false;

        $physicalDir = str_replace('\\', '/', BASE_PATH . '/public/uploads/' . $subfolder . '/');
        $dbDir = 'uploads/' . $subfolder . '/';

        if (!file_exists($physicalDir)) {
            if (!@mkdir($physicalDir, 0777, true)) {
                return false;
            }
        }

        if (!$filename) {
            $filename = uniqid($subfolder . '_', true) . '.webp';
        }
        
        $fullDestination = $physicalDir . $filename;
        $dbPath = $dbDir . $filename;

        if (@imagewebp($this->image, $fullDestination, $this->quality)) {
            return $dbPath;
        }

        return false;
    }

    /**
     * Helper to destroy image in memory
     */
    public function __destruct()
    {
        if ($this->image && is_resource($this->image)) {
            imagedestroy($this->image);
        }
    }

    /**
     * Static helper for single upload
     */
    public static function upload($file, $subfolder = 'products')
    {
        $processor = new self($file);
        return $processor->saveAsWebp($subfolder);
    }
}
