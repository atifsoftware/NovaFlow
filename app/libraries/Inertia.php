<?php

namespace NovaFlow\Core;

use Closure;

/**
 * Inertia.js Protocol Adapter for NovaFlow PHP
 * Bridges PHP Controllers with modern frontend components (React, Vue, Svelte) without REST APIs.
 */
class Inertia
{
    protected static array $shared = [];
    protected static mixed $version = null;
    protected static string $rootView = 'app';

    /**
     * Share data globally with all Inertia views
     */
    public static function share(string|array $key, mixed $value = null): void
    {
        if (is_array($key)) {
            self::$shared = array_merge(self::$shared, $key);
        } else {
            self::$shared[$key] = $value;
        }
    }

    /**
     * Get shared data
     */
    public static function getShared(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return self::$shared;
        }
        return self::$shared[$key] ?? $default;
    }

    /**
     * Flush/clear shared data (useful for test isolation)
     */
    public static function flushShared(): void
    {
        self::$shared = [];
    }

    /**
     * Set the asset version or version resolver callback
     */
    public static function version(mixed $version): void
    {
        self::$version = $version;
    }

    /**
     * Get the asset version (supports explicit string, closure, or Vite manifest hash)
     */
    public static function getVersion(): ?string
    {
        if (is_callable(self::$version)) {
            return (string) call_user_func(self::$version);
        }

        if (self::$version !== null) {
            return (string) self::$version;
        }

        // Automatic cache-busting from Vite manifest.json
        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        $manifestPath = $basePath . '/public/build/manifest.json';
        if (file_exists($manifestPath)) {
            return md5_file($manifestPath);
        }

        return defined('APP_VERSION') ? APP_VERSION : '1.0.0';
    }

    /**
     * Set the root view template (default: 'app')
     */
    public static function setRootView(string $view): void
    {
        self::$rootView = $view;
    }

    /**
     * Get the root view template
     */
    public static function getRootView(): string
    {
        return self::$rootView;
    }

    /**
     * Resolve an absolute URL for redirect and location headers
     */
    public static function resolveAbsoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                 (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) ||
                 (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        $scheme = $https ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePrefix = '';

        if (defined('BASE_URL') && !empty(BASE_URL)) {
            $parsedBase = parse_url(BASE_URL);
            if (!empty($parsedBase['scheme'])) {
                $scheme = $parsedBase['scheme'] . '://';
            }
            if (!empty($parsedBase['host'])) {
                $host = $parsedBase['host'] . (!empty($parsedBase['port']) ? ':' . $parsedBase['port'] : '');
            }
            if (!empty($parsedBase['path'])) {
                $basePrefix = '/' . trim($parsedBase['path'], '/');
            }
        }

        // Clean target path
        $path = '/' . ltrim($url, '/');

        // If BASE_URL has a subfolder prefix (e.g. /NovaFlow), ensure it is prepended if not already present
        if (!empty($basePrefix) && $basePrefix !== '/') {
            if ($path !== $basePrefix && !str_starts_with($path, $basePrefix . '/')) {
                $path = $basePrefix . $path;
            }
        }

        return $scheme . $host . $path;
    }

    /**
     * Render an Inertia component with props
     */
    public static function render(string $component, array $props = []): void
    {
        $isInertia = (Request::header('X-Inertia') === 'true') || 
                     (isset($_SERVER['HTTP_X_INERTIA']) && $_SERVER['HTTP_X_INERTIA'] === 'true');

        $version = self::getVersion();

        // 1. Asset version check (Inertia Protocol: 409 Conflict)
        if ($isInertia && Request::method() === 'GET' && !empty($version)) {
            $clientVersion = Request::header('X-Inertia-Version') ?? $_SERVER['HTTP_X_INERTIA_VERSION'] ?? null;
            if ($clientVersion !== null && $clientVersion !== $version) {
                if (ob_get_length()) ob_clean();
                http_response_code(409);
                header('X-Inertia: true');
                $targetUrl = self::resolveAbsoluteUrl($_SERVER['REQUEST_URI'] ?? '/');
                header('X-Inertia-Location: ' . $targetUrl);
                if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
                    exit;
                }
                return;
            }
        }

        // 2. Merge global shared props + passed props
        $mergedProps = array_merge(self::$shared, $props);

        // Share baseUrl automatically for subfolder compatibility
        if (defined('BASE_URL') && !empty(BASE_URL) && !isset($mergedProps['baseUrl'])) {
            $mergedProps['baseUrl'] = rtrim(BASE_URL, '/');
        }

        // 3. Partial reload support (X-Inertia-Partial-Data & X-Inertia-Partial-Except)
        $partialComponent = Request::header('X-Inertia-Partial-Component') ?? $_SERVER['HTTP_X_INERTIA_PARTIAL_COMPONENT'] ?? null;
        if ($isInertia && $partialComponent === $component) {
            $partialData = Request::header('X-Inertia-Partial-Data') ?? $_SERVER['HTTP_X_INERTIA_PARTIAL_DATA'] ?? '';
            $partialExcept = Request::header('X-Inertia-Partial-Except') ?? $_SERVER['HTTP_X_INERTIA_PARTIAL_EXCEPT'] ?? '';

            $only = array_filter(array_map('trim', explode(',', $partialData)));
            $except = array_filter(array_map('trim', explode(',', $partialExcept)));

            if (!empty($only)) {
                $filtered = [];
                foreach ($only as $dotKey) {
                    $rootKey = explode('.', $dotKey)[0];
                    if (array_key_exists($rootKey, $mergedProps)) {
                        $val = $mergedProps[$rootKey];
                        $resolved = ($val instanceof Closure) ? $val() : $val;
                        if ($rootKey === $dotKey) {
                            $filtered[$rootKey] = $resolved;
                        } else {
                            $nestedPath = substr($dotKey, strlen($rootKey) + 1);
                            $nestedVal = self::getDotValue($resolved, $nestedPath);
                            self::setDotValue($filtered, $dotKey, $nestedVal);
                        }
                    }
                }
                $mergedProps = $filtered;
            } elseif (!empty($except)) {
                foreach ($except as $dotKey) {
                    if (str_contains($dotKey, '.')) {
                        self::unsetDotValue($mergedProps, $dotKey);
                    } else {
                        unset($mergedProps[$dotKey]);
                    }
                }
                // Evaluate remaining lazy props
                foreach ($mergedProps as $key => $val) {
                    if ($val instanceof Closure) {
                        $mergedProps[$key] = $val();
                    }
                }
            } else {
                foreach ($mergedProps as $key => $val) {
                    if ($val instanceof Closure) {
                        $mergedProps[$key] = $val();
                    }
                }
            }
        } else {
            // Full load: evaluate all lazy Closure props
            foreach ($mergedProps as $key => $val) {
                if ($val instanceof Closure) {
                    $mergedProps[$key] = $val();
                }
            }
        }

        $url = $_SERVER['REQUEST_URI'] ?? '/';

        $page = [
            'component' => $component,
            'props'     => $mergedProps,
            'url'       => $url,
            'version'   => $version,
        ];

        // 4. Inertia AJAX response
        if ($isInertia) {
            if (ob_get_length()) ob_clean();
            http_response_code(200);
            header('Content-Type: application/json; charset=utf-8');
            header('Vary: X-Inertia');
            header('X-Inertia: true');
            echo json_encode($page, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
                exit;
            }
            return;
        }

        // 5. Initial Full HTML Page Visit
        $rootTemplatePath = (defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2)) . '/app/views/' . self::$rootView . '.php';

        if (file_exists($rootTemplatePath)) {
            if (ob_get_length()) ob_clean();
            header('Content-Type: text/html; charset=utf-8');
            header('Vary: X-Inertia');
            extract(['page' => $page, 'component' => $component]);
            include $rootTemplatePath;
            if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
                exit;
            }
            return;
        }

        // 6. Default clean HTML shell fallback if custom view is missing
        $escapedPage = htmlspecialchars(json_encode($page, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
        $compName = htmlspecialchars($component, ENT_QUOTES, 'UTF-8');

        if (ob_get_length()) ob_clean();
        header('Content-Type: text/html; charset=utf-8');
        header('Vary: X-Inertia');
        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{$compName}</title>
</head>
<body style="margin:0;background:#0f172a;color:#f8fafc;font-family:sans-serif">
    <div id="app" data-page="{$escapedPage}"></div>
</body>
</html>
HTML;
        if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
            exit;
        }
    }

    /**
     * Perform an external or full-page redirect under the Inertia protocol
     */
    public static function location(string $url): void
    {
        $isInertia = (Request::header('X-Inertia') === 'true') || 
                     (isset($_SERVER['HTTP_X_INERTIA']) && $_SERVER['HTTP_X_INERTIA'] === 'true');

        $targetUrl = self::resolveAbsoluteUrl($url);

        if ($isInertia) {
            if (ob_get_length()) ob_clean();
            http_response_code(409);
            header('X-Inertia: true');
            header('X-Inertia-Location: ' . $targetUrl);
            if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
                exit;
            }
            return;
        }

        header('Location: ' . $targetUrl);
        if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
            exit;
        }
    }

    /**
     * Dot notation reader
     */
    protected static function getDotValue(mixed $target, string $key): mixed
    {
        foreach (explode('.', $key) as $segment) {
            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
            } else {
                return null;
            }
        }
        return $target;
    }

    /**
     * Dot notation setter
     */
    protected static function setDotValue(array &$target, string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $k = array_shift($keys);
            if (!isset($target[$k]) || !is_array($target[$k])) {
                $target[$k] = [];
            }
            $target = &$target[$k];
        }
        $target[array_shift($keys)] = $value;
    }

    /**
     * Dot notation unsetter
     */
    protected static function unsetDotValue(array &$target, string $key): void
    {
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $k = array_shift($keys);
            if (!isset($target[$k]) || !is_array($target[$k])) {
                return;
            }
            $target = &$target[$k];
        }
        unset($target[array_shift($keys)]);
    }
}
