<?php

namespace App\Services;

/**
 * Cloudinary Cloud Storage Service
 * Provides automated, high-performance cloud media uploads, URL generation, 
 * dynamic transformations, and global CDN delivery.
 */
class CloudinaryService
{
    /**
     * Safe environment getter across all frameworks and standalone runtimes
     */
    public static function getEnv(string $key, mixed $default = null): mixed
    {
        if (function_exists('env')) {
            $val = \env($key);
            if ($val !== null && $val !== '') {
                return $val;
            }
        }
        if (class_exists('\\NovaFlow\\Core\\Dotenv')) {
            $val = \NovaFlow\Core\Dotenv::get($key);
            if ($val !== null && $val !== '') {
                return $val;
            }
        }
        return $_ENV[$key] ?? ($_SERVER[$key] ?? $default);
    }

    /**
     * Check if Cloudinary is configured and enabled in environment
     */
    public static function isConfigured(): bool
    {
        $enabled = self::getEnv('CLOUDINARY_ENABLED', true);
        if ($enabled === 'false' || $enabled === '0' || $enabled === false) {
            return false;
        }

        $cloudName = self::getCloudName();
        $apiKey    = self::getApiKey();
        $apiSecret = self::getApiSecret();

        return !empty($cloudName) && !empty($apiKey) && !empty($apiSecret);
    }

    public static function getCloudName(): string
    {
        return (string)self::getEnv('CLOUDINARY_CLOUD_NAME', '');
    }

    public static function getApiKey(): string
    {
        return (string)self::getEnv('CLOUDINARY_API_KEY', '');
    }

    public static function getApiSecret(): string
    {
        return (string)self::getEnv('CLOUDINARY_API_SECRET', '');
    }

    public static function getDefaultFolder(): string
    {
        return (string)self::getEnv('CLOUDINARY_FOLDER', 'fnfshop_media');
    }

    /**
     * Upload an image to Cloudinary
     * 
     * @param string|array $file Local file path, remote URL, or $_FILES element
     * @param string|null $folder Target folder (appended to root folder)
     * @param string|null $publicId Optional custom public id
     * @param array $options Additional Cloudinary parameters (tags, etc.)
     * @return array|null Returns array with 'secure_url', 'public_id' or null on failure
     */
    public static function upload($file, ?string $folder = null, ?string $publicId = null, array $options = []): ?array
    {
        if (!self::isConfigured()) {
            return null;
        }

        $cloudName = self::getCloudName();
        $apiKey    = self::getApiKey();
        $apiSecret = self::getApiSecret();

        // Determine source
        $isRemoteUrl = false;
        $filePath = '';

        if (is_array($file)) {
            $filePath = $file['tmp_name'] ?? '';
        } elseif (is_string($file)) {
            if (str_starts_with($file, 'http://') || str_starts_with($file, 'https://')) {
                $isRemoteUrl = true;
                $filePath = $file;
            } else {
                $filePath = $file;
            }
        }

        if (empty($filePath)) {
            return null;
        }

        if (!$isRemoteUrl && !file_exists($filePath)) {
            return null;
        }

        // Determine destination folder
        $baseFolder = trim(self::getDefaultFolder(), '/');
        $targetFolder = $baseFolder;
        if (!empty($folder)) {
            $cleanSub = trim($folder, '/');
            if ($cleanSub !== $baseFolder && !str_starts_with($cleanSub, $baseFolder . '/')) {
                $targetFolder = $baseFolder . '/' . $cleanSub;
            } else {
                $targetFolder = $cleanSub;
            }
        }

        $timestamp = time();
        $paramsToSign = [
            'folder'    => $targetFolder,
            'timestamp' => $timestamp
        ];

        if (!empty($publicId)) {
            $paramsToSign['public_id'] = $publicId;
        }

        if (!empty($options['tags'])) {
            $paramsToSign['tags'] = is_array($options['tags']) ? implode(',', $options['tags']) : $options['tags'];
        }

        // Sign request parameters
        ksort($paramsToSign);
        $signParts = [];
        foreach ($paramsToSign as $k => $v) {
            $signParts[] = "{$k}={$v}";
        }
        $toSign = implode('&', $signParts) . $apiSecret;
        $signature = sha1($toSign);

        $postData = array_merge($paramsToSign, [
            'api_key'   => $apiKey,
            'signature' => $signature
        ]);

        if ($isRemoteUrl) {
            $postData['file'] = $filePath;
        } else {
            $postData['file'] = new \CURLFile($filePath);
        }

        $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if (!empty($data['secure_url'])) {
                if (class_exists('\\NovaFlow\\Core\\Logger')) {
                    \NovaFlow\Core\Logger::info("Cloudinary upload successful: " . $data['secure_url'], 'cloudinary');
                }
                return [
                    'status'     => 'success',
                    'secure_url' => $data['secure_url'],
                    'url'        => $data['url'] ?? $data['secure_url'],
                    'public_id'  => $data['public_id'] ?? '',
                    'format'     => $data['format'] ?? '',
                    'bytes'      => $data['bytes'] ?? 0,
                    'width'      => $data['width'] ?? 0,
                    'height'     => $data['height'] ?? 0
                ];
            }
        }

        if (class_exists('\\NovaFlow\\Core\\Logger')) {
            \NovaFlow\Core\Logger::error("Cloudinary upload failed (HTTP $httpCode): " . ($response ?: $curlError), 'cloudinary');
        }

        return null;
    }

    /**
     * Upload an image from a remote URL to Cloudinary
     */
    public static function uploadUrl(string $url, ?string $folder = null, ?string $publicId = null): ?array
    {
        return self::upload($url, $folder, $publicId);
    }

    /**
     * Delete an image from Cloudinary by its public ID
     */
    public static function delete(string $publicId): bool
    {
        if (!self::isConfigured() || empty($publicId)) {
            return false;
        }

        $cloudName = self::getCloudName();
        $apiKey    = self::getApiKey();
        $apiSecret = self::getApiSecret();

        $timestamp = time();
        $paramsToSign = [
            'public_id' => $publicId,
            'timestamp' => $timestamp
        ];
        ksort($paramsToSign);

        $signParts = [];
        foreach ($paramsToSign as $k => $v) {
            $signParts[] = "{$k}={$v}";
        }
        $toSign = implode('&', $signParts) . $apiSecret;
        $signature = sha1($toSign);

        $postData = [
            'public_id' => $publicId,
            'timestamp' => $timestamp,
            'api_key'   => $apiKey,
            'signature' => $signature
        ];

        $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            return ($data['result'] ?? '') === 'ok';
        }

        return false;
    }

    /**
     * Generate or transform a full Cloudinary or Asset URL
     * 
     * Supports on-the-fly dynamic image optimization:
     * e.g. ['w' => 500, 'h' => 500, 'c' => 'fill', 'q' => 'auto', 'f' => 'auto']
     * 
     * @param string|null $source Cloudinary URL, public ID, local path, or external URL
     * @param array $transformations Transformation options (w, h, c, q, f, r, etc.)
     * @param string $default Fallback URL if source is empty
     * @return string
     */
    public static function url(?string $source, array $transformations = [], string $default = ''): string
    {
        if (empty($source)) {
            if (!empty($default)) {
                return $default;
            }
            return function_exists('asset') ? \asset('assets/img/fnf_logo.png') : '/assets/img/fnf_logo.png';
        }

        // 1. If it's already a Cloudinary URL
        if (str_contains($source, 'res.cloudinary.com')) {
            if (!empty($transformations)) {
                return self::transformUrl($source, $transformations);
            }
            return $source;
        }

        // 2. If it's another external HTTP/HTTPS URL
        if (str_starts_with($source, 'http://') || str_starts_with($source, 'https://')) {
            return $source;
        }

        // 3. If source contains an embedded URL (e.g. accidental uploads/products/https://...)
        if (preg_match('/(https?:\/\/[^\s\'"]+)/', $source, $m)) {
            $matchedUrl = $m[1];
            if (str_contains($matchedUrl, 'res.cloudinary.com') && !empty($transformations)) {
                return self::transformUrl($matchedUrl, $transformations);
            }
            return $matchedUrl;
        }

        // 4. Local asset resolution fallback
        $clean = ltrim($source, '/');
        if (function_exists('asset')) {
            if (str_starts_with($clean, 'uploads/') || str_starts_with($clean, 'assets/') || str_starts_with($clean, 'img/')) {
                return \asset($clean);
            }
            // If just a filename, assume product upload directory
            if (defined('BASE_PATH') && file_exists(BASE_PATH . '/public/uploads/products/' . $clean)) {
                return \asset('uploads/products/' . $clean);
            }
            return \asset('uploads/' . $clean);
        }

        $base = defined('BASE_URL') ? BASE_URL : '';
        return $base . '/' . $clean;
    }

    /**
     * Inject dynamic transformations into a Cloudinary URL
     * Converts ['w' => 400, 'h' => 400, 'c' => 'fill', 'q' => 'auto', 'f' => 'auto']
     * into https://res.cloudinary.com/{cloud}/image/upload/c_fill,f_auto,h_400,q_auto,w_400/...
     */
    public static function transformUrl(string $cloudinaryUrl, array $transformations): string
    {
        if (empty($transformations)) {
            return $cloudinaryUrl;
        }

        $parts = [];
        foreach ($transformations as $key => $val) {
            $k = match($key) {
                'width', 'w'     => 'w',
                'height', 'h'    => 'h',
                'crop', 'c'      => 'c',
                'quality', 'q'   => 'q',
                'format', 'f'    => 'f',
                'radius', 'r'    => 'r',
                'gravity', 'g'   => 'g',
                'effect', 'e'    => 'e',
                default          => $key
            };
            $parts[] = "{$k}_{$val}";
        }
        sort($parts);
        $transString = implode(',', $parts);

        // Pattern: .../image/upload/(v\d+/... or public_id)
        if (preg_match('#(/image/upload/)(.*)$#i', $cloudinaryUrl, $m)) {
            $prefix = $m[1];
            $rest   = $m[2];

            // If transformations already present, replace or append
            if (preg_match('#^([a-z]_[a-z0-9_,]+)/(.*)$#i', $rest, $rm)) {
                $rest = $rm[2];
            }
            return str_replace($m[0], $prefix . $transString . '/' . $rest, $cloudinaryUrl);
        }

        return $cloudinaryUrl;
    }

    /**
     * Sync an existing local file to Cloudinary and return its secure URL
     */
    public static function syncLocalFile(string $fullOrRelativePath, ?string $subfolder = null): ?string
    {
        $fullPath = $fullOrRelativePath;
        if (!file_exists($fullPath) && defined('BASE_PATH')) {
            $fullPath = BASE_PATH . '/public/' . ltrim($fullOrRelativePath, '/');
        }

        if (!file_exists($fullPath)) {
            return null;
        }

        $folder = $subfolder ?: 'products';
        $res = self::upload($fullPath, $folder);
        return $res['secure_url'] ?? null;
    }
}

// Register universal alias for NovaFlow\Core\CloudinaryService compatibility
if (!class_exists('NovaFlow\Core\CloudinaryService', false)) {
    class_alias(CloudinaryService::class, 'NovaFlow\\Core\\CloudinaryService');
}
