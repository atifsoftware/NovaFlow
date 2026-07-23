<?php

namespace NovaFlow\Core;

use Exception;

/**
 * UploadHandler
 * Secure file upload handling with validation and processing
 */
class UploadHandler
{
    protected string $uploadPath;
    protected array $allowedTypes = [];
    protected array $allowedExtensions = [];
    protected int $maxSize = 5242880; // 5MB default
    protected array $errors = [];
    protected bool $generateNewName = true;
    protected array $imageSettings = [];

    public function __construct(string $uploadPath = null)
    {
        $this->uploadPath = $uploadPath ?? dirname(__DIR__, 2) . '/public/uploads/';
        
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }

        $this->allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'text/csv',
            'application/zip'
        ];

        $this->allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'csv', 'zip'];
    }

    /**
     * Set allowed file types
     */
    public function allowedTypes(array $types): self
    {
        $this->allowedTypes = $types;
        return $this;
    }

    /**
     * Set allowed file extensions
     */
    public function allowedExtensions(array $extensions): self
    {
        $this->allowedExtensions = array_map('strtolower', $extensions);
        return $this;
    }

    protected function getRealMimeType(string $tmpPath): ?string
    {
        if (class_exists(\finfo::class)) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            return $finfo->file($tmpPath) ?: null;
        }
        if (function_exists('mime_content_type')) {
            return mime_content_type($tmpPath) ?: null;
        }
        return null;
    }

    /**
     * Set max file size in bytes
     */
    public function maxSize(int $bytes): self
    {
        $this->maxSize = $bytes;
        return $this;
    }

    /**
     * Enable/disable auto-generate new filename
     */
    public function generateNewName(bool $value): self
    {
        $this->generateNewName = $value;
        return $this;
    }

    /**
     * Configure image processing
     */
    public function processImage(int $maxWidth = null, int $maxHeight = null, int $quality = 85): self
    {
        $this->imageSettings = [
            'maxWidth' => $maxWidth,
            'maxHeight' => $maxHeight,
            'quality' => $quality
        ];
        return $this;
    }

    /**
     * Upload single file
     */
    public function upload(string $fieldName): array
    {
        $this->errors = [];
        
        if (!isset($_FILES[$fieldName])) {
            return $this->error('No file uploaded');
        }

        $file = $_FILES[$fieldName];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->error($this->getUploadErrorMessage($file['error']));
        }

        // Secure file signature validation on server
        $realMime = $this->getRealMimeType($file['tmp_name']);
        if (!$realMime || !in_array($realMime, $this->allowedTypes)) {
            return $this->error('File type not allowed');
        }

        // File extension validation
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!empty($this->allowedExtensions) && !in_array($ext, $this->allowedExtensions)) {
            return $this->error('File extension not allowed');
        }

        if ($file['size'] > $this->maxSize) {
            return $this->error('File size exceeds limit');
        }

        $filename = $this->generateNewName ? $this->generateFilename($file['name']) : $file['name'];
        $destination = $this->uploadPath . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return $this->error('Failed to move uploaded file');
        }

        if ($this->isImage($realMime)) {
            $this->processImageFile($destination);
        }

        return [
            'success' => true,
            'filename' => $filename,
            'original_name' => $file['name'],
            'size' => $file['size'],
            'type' => $realMime,
            'path' => '/uploads/' . $filename
        ];
    }

    /**
     * Upload multiple files
     */
    public function uploadMultiple(string $fieldName): array
    {
        $this->errors = [];
        
        if (!isset($_FILES[$fieldName])) {
            return [$this->error('No files uploaded')];
        }

        $files = $_FILES[$fieldName];
        
        if (!is_array($files['name'])) {
            return [$this->upload($fieldName)];
        }

        $results = [];
        $count = count($files['name']);

        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $results[] = $this->error($this->getUploadErrorMessage($files['error'][$i]));
                continue;
            }

            // Secure file signature validation on server
            $realMime = $this->getRealMimeType($files['tmp_name'][$i]);
            if (!$realMime || !in_array($realMime, $this->allowedTypes)) {
                $results[] = $this->error('File type not allowed: ' . $files['name'][$i]);
                continue;
            }

            // File extension validation
            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            if (!empty($this->allowedExtensions) && !in_array($ext, $this->allowedExtensions)) {
                $results[] = $this->error('File extension not allowed: ' . $files['name'][$i]);
                continue;
            }

            if ($files['size'][$i] > $this->maxSize) {
                $results[] = $this->error('File size exceeds limit: ' . $files['name'][$i]);
                continue;
            }

            $filename = $this->generateNewName ? $this->generateFilename($files['name'][$i]) : $files['name'][$i];
            $destination = $this->uploadPath . $filename;

            if (!move_uploaded_file($files['tmp_name'][$i], $destination)) {
                $results[] = $this->error('Failed to move uploaded file: ' . $files['name'][$i]);
                continue;
            }

            if ($this->isImage($realMime)) {
                $this->processImageFile($destination);
            }

            $results[] = [
                'success' => true,
                'filename' => $filename,
                'original_name' => $files['name'][$i],
                'size' => $files['size'][$i],
                'type' => $realMime,
                'path' => '/uploads/' . $filename
            ];
        }

        return $results;
    }

    /**
     * Delete uploaded file
     */
    public function delete(string $filename): bool
    {
        $path = $this->uploadPath . $filename;
        
        if (file_exists($path)) {
            return unlink($path);
        }
        
        return false;
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function generateFilename(string $original): string
    {
        $ext = pathinfo($original, PATHINFO_EXTENSION);
        $name = bin2hex(random_bytes(8));
        return $name . '.' . strtolower($ext);
    }

    protected function isImage(string $type): bool
    {
        return str_starts_with($type, 'image/');
    }

    protected function processImageFile(string $path): void
    {
        if (empty($this->imageSettings)) {
            return;
        }

        if (!extension_loaded('gd')) {
            return;
        }

        $info = getimagesize($path);
        
        if (!$info) {
            return;
        }

        [$width, $height] = $info;
        [$maxWidth, $maxHeight] = [
            $this->imageSettings['maxWidth'] ?? $width,
            $this->imageSettings['maxHeight'] ?? $height
        ];

        if ($width <= $maxWidth && $height <= $maxHeight) {
            return;
        }

        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        switch ($info['mime']) {
            case 'image/jpeg':
                $src = imagecreatefromjpeg($path);
                $dst = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagejpeg($dst, $path, $this->imageSettings['quality'] ?? 85);
                break;
            case 'image/png':
                $src = imagecreatefrompng($path);
                $dst = imagecreatetruecolor($newWidth, $newHeight);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagepng($dst, $path);
                break;
            case 'image/webp':
                $src = imagecreatefromwebp($path);
                $dst = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagewebp($dst, $path, $this->imageSettings['quality'] ?? 85);
                break;
        }

        imagedestroy($src);
        imagedestroy($dst);
    }

    protected function getUploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds server limit',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit',
            UPLOAD_ERR_PARTIAL => 'File partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file uploaded',
            default => 'Upload error'
        };
    }

    protected function error(string $message): array
    {
        $this->errors[] = $message;
        return ['success' => false, 'message' => $message];
    }
}