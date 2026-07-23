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
     * Get the CSRF token
     */
    function csrf_token()
    {
        return \NovaFlow\Core\Security::generateCSRFToken();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF token field for forms
     */
    function csrf_field()
    {
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
    }
}

if (!function_exists('base_url')) {
    /**
     * Get the base URL
     */
    function base_url($path = '')
    {
        return BASE_URL . $path;
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

if (!function_exists('acl_check')) {
    /**
     * Check user permissions (ACL)
     */
    function acl_check(string $permission): bool {
        $userRole = $_SESSION['user_role'] ?? 'guest';
        $permissions = [
            'admin' => ['*'],
            'manager' => ['read', 'write', 'edit'],
            'user' => ['read']
        ];
        return in_array($permission, $permissions[$userRole] ?? []) || in_array('*', $permissions[$userRole] ?? []);
    }
}
