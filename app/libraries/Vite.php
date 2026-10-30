<?php

namespace NovaFlow\Core;

/**
 * Vite Asset Bridge for NovaFlow PHP
 * Automatically switches between Vite HMR dev server (when running npm run dev) 
 * and production hashed assets from manifest.json (when running npm run build).
 */
class Vite
{
    protected static ?array $manifest = null;

    /**
     * Generate HTML tags for scripts and stylesheets
     */
    public static function tags(string $entry = 'resources/js/app.jsx'): string
    {
        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        $hotFile = $basePath . '/public/hot';

        // 1. Dev / HMR Mode (Vite server running)
        if (file_exists($hotFile)) {
            $hotUrl = rtrim(trim((string) @file_get_contents($hotFile)), '/');
            if (!empty($hotUrl) && self::isDevServerRunning($hotUrl)) {
                return <<<HTML
    <script type="module" src="{$hotUrl}/@vite/client"></script>
    <script type="module" src="{$hotUrl}/{$entry}"></script>
HTML;
            }

            // Stale hot file detected (server was terminated or force-killed)
            // Self-heal: automatically remove the dead hot file and fall back to production assets
            @unlink($hotFile);
        }

        // 2. Production Mode (Manifest lookup)
        $manifestPath = $basePath . '/public/build/manifest.json';
        if (file_exists($manifestPath)) {
            if (self::$manifest === null) {
                $content = file_get_contents($manifestPath);
                self::$manifest = json_decode($content, true) ?: [];
            }

            if (isset(self::$manifest[$entry])) {
                $item = self::$manifest[$entry];
                $html = '';

                // Load CSS bundle if present
                if (!empty($item['css']) && is_array($item['css'])) {
                    foreach ($item['css'] as $cssFile) {
                        $html .= "<link rel=\"stylesheet\" href=\"{$baseUrl}/build/{$cssFile}\" />\n    ";
                    }
                }

                // Load JS entry bundle
                $jsFile = $item['file'] ?? '';
                if ($jsFile) {
                    $html .= "<script type=\"module\" src=\"{$baseUrl}/build/{$jsFile}\"></script>";
                }

                return $html;
            }
        }

        // 3. Fallback: Search for any compiled app JS and CSS bundles in build directory
        $html = '';
        $cssGlob = glob($basePath . '/public/build/assets/app-*.css');
        if (!empty($cssGlob)) {
            $latestCss = basename($cssGlob[0]);
            $html .= "<link rel=\"stylesheet\" href=\"{$baseUrl}/build/assets/{$latestCss}\" />\n    ";
        } elseif (file_exists($basePath . '/public/build/assets/app.css')) {
            $html .= "<link rel=\"stylesheet\" href=\"{$baseUrl}/build/assets/app.css\" />\n    ";
        }

        $jsGlob = glob($basePath . '/public/build/assets/app-*.js');
        if (!empty($jsGlob)) {
            $latestJs = basename($jsGlob[0]);
            $html .= "<script type=\"module\" src=\"{$baseUrl}/build/assets/{$latestJs}\"></script>";
        } else {
            $html .= "<script type=\"module\" src=\"{$baseUrl}/build/assets/app.js\"></script>";
        }

        return $html;
    }

    /**
     * Verify whether the Vite dev server is actively listening
     */
    public static function isDevServerRunning(string $url): bool
    {
        $parts = parse_url($url);
        if (!$parts || empty($parts['host'])) {
            return false;
        }

        $host = trim($parts['host'], '[]'); // strip brackets if IPv6
        $port = isset($parts['port']) ? (int)$parts['port'] : ($parts['scheme'] === 'https' ? 443 : 80);

        // Fast socket connection probe with 0.05s timeout.
        // Wrapped in a local error handler because a failed connection raises a
        // warning, and custom global handlers may ignore both `@` and
        // error_reporting(). Nothing may be printed into the response here.
        set_error_handler(static fn (): bool => true, E_WARNING | E_NOTICE);
        try {
            $connection = fsockopen($host, $port, $errno, $errstr, 0.05);
        } finally {
            restore_error_handler();
        }

        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }

        return false;
    }

    /**
     * Flush cached manifest (useful in testing)
     */
    public static function flush(): void
    {
        self::$manifest = null;
    }
}
