<?php

namespace NovaFlow\Core;
// ============================================================
//  ImageHandler — Product/Banner Image Upload Helper
// ============================================================
class ImageHandler
{
    private array $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private int $maxSize = 5 * 1024 * 1024; // 5MB
    
    public function upload(array $file, string $folder = 'products'): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Upload error: ' . $file['error']];
        }
        
        if ($file['size'] > $this->maxSize) {
            return ['success' => false, 'message' => 'File too large (max 5MB)'];
        }
        
        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $this->allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, WebP allowed.'];
        }
        
        $uploadDir = BASE_PATH . '/public/uploads/' . $folder . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $ext = match($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => 'jpg',
        };
        
        $safePrefix = str_replace(['/', '\\', '.', ' '], '_', $folder);
        $filename   = uniqid($safePrefix . '_', true) . '.' . $ext;
        $destPath   = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'message' => 'Failed to save file'];
        }
        
        return [
            'success' => true,
            'path'    => 'uploads/' . $folder . '/' . $filename,
            'url'     => BASE_URL . '/public/uploads/' . $folder . '/' . $filename,
        ];
    }
}
