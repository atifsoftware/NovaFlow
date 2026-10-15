<?php
/**
 * NovaFlow Global Helper Functions
 */

if (!function_exists('env')) {
    /**
     * Get environment variable
     */
    function env($key, $default = null) {
        return \NovaFlow\Core\Dotenv::get($key, $default);
    }
}

if (!function_exists('flash')) {
    /**
     * Sets or displays a flash message
     */
    function flash($name = '', $message = '', $class = 'alert alert-success')
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!empty($name)) {
            if (!empty($message) && empty($_SESSION[$name])) {
                if (!empty($_SESSION[$name])) {
                    unset($_SESSION[$name]);
                }
                if (!empty($_SESSION[$name . '_class'])) {
                    unset($_SESSION[$name . '_class']);
                }

                $_SESSION[$name] = $message;
                $_SESSION[$name . '_class'] = $class;
            } elseif (empty($message) && !empty($_SESSION[$name])) {
                $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : '';
                $msg = $_SESSION[$name];

                $type = ($name == 'error' || strpos($class, 'danger') !== false) ? 'error' : 'success';
                
                // Alert format
                echo '<div class="' . $class . ' alert-dismissible fade show shadow-sm border-0 rounded-3 mb-3" role="alert" id="msg-flash">
                    <div class="d-flex align-items-center">
                        <i class="fas ' . ($type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle') . ' me-2"></i>
                        <div>' . $msg . '</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';

                unset($_SESSION[$name]);
                unset($_SESSION[$name . '_class']);
            }
        }
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get CSRF Token
     */
    function csrf_token(): string
    {
        return \NovaFlow\Core\Security::getCSRFToken();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF Hidden Input Field
     */
    function csrf_field(): string
    {
        $token = csrf_token();
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }
}

if (!function_exists('view')) {
    /**
     * Shorthand to render a view
     */
    function view(string $view, array $data = [], string $layout = 'main') {
        $viewService = \NovaFlow\Core\Container::getInstance()->make(\NovaFlow\Core\View::class);
        echo $viewService->render($view, $data, $layout);
    }
}

if (!function_exists('request')) {
    /**
     * Get request instance or input value
     */
    function request($key = null, $default = null) {
        if ($key === null) {
            return \NovaFlow\Core\Container::getInstance()->make(\NovaFlow\Core\Request::class);
        }
        return \NovaFlow\Core\Request::input($key, $default);
    }
}

if (!function_exists('redirect')) {
    /**
     * Quick redirect
     */
    function redirect(string $path) {
        header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
        exit;
    }
}

if (!function_exists('url')) {
    /**
     * Generate URL
     */
    function url(string $path = '') {
        return BASE_URL . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Generate Asset URL
     */
    function asset(string $path) {
        return BASE_URL . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('db')) {
    /**
     * Get database driver instance
     */
    function db() {
        return \NovaFlow\Core\Container::getInstance()->make(\NovaFlow\Core\DatabaseInterface::class);
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and Die
     */
    function dd(...$vars) {
        echo '<div style="background: #18171B; color: #FFF; padding: 20px; border-radius: 8px; margin: 10px; font-family: monospace; font-size: 14px; overflow: auto;">';
        foreach ($vars as $v) {
            echo '<pre>';
            var_dump($v);
            echo '</pre><hr style="opacity: 0.1">';
        }
        echo '</div>';
        die();
    }
}

if (!function_exists('config')) {
    /**
     * Get config value (from environment or constants)
     */
    function config($key, $default = null) {
        if (defined($key)) return constant($key);
        return env($key, $default);
    }
}

if (!function_exists('__')) {
    /**
     * Localize translation key
     */
    function __($key, $default = '') {
        static $translations = [];
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_GET['lang']) && in_array($_GET['lang'], ['bn', 'en'])) {
            $_SESSION['lang'] = $_GET['lang'];
        }
        
        $lang = $_SESSION['lang'] ?? 'bn';
        
        if (!isset($translations[$lang])) {
            $file = BASE_PATH . "/app/lang/{$lang}.php";
            if (file_exists($file)) {
                $translations[$lang] = require $file;
            } else {
                $translations[$lang] = [];
            }
        }
        
        return $translations[$lang][$key] ?? ($default ?: $key);
    }
}

if (!function_exists('current_lang')) {
    /**
     * Get current active language
     */
    function current_lang() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['lang'] ?? 'bn';
    }
}

if (!function_exists('event')) {
    /**
     * Dispatch an event with optional payload
     */
    function event(string|object $event, mixed $payload = []): array {
        return \NovaFlow\Core\Event::dispatch($event, $payload);
    }
}

if (!function_exists('old')) {
    /**
     * Get old flashed input from session
     */
    function old(?string $key = null, mixed $default = null): mixed {
        return \NovaFlow\Core\Request::old($key, $default);
    }
}

if (!function_exists('errors')) {
    /**
     * Get validation errors from session flash
     */
    function errors(?string $field = null): mixed {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $errors = $_SESSION['_flash_errors'] ?? [];
        if ($field !== null) {
            return $errors[$field][0] ?? null;
        }
        return $errors;
    }
}

if (!function_exists('has_error')) {
    /**
     * Check if a specific input field has a validation error
     */
    function has_error(string $field): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return !empty($_SESSION['_flash_errors'][$field]);
    }
}

