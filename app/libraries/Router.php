<?php

namespace NovaFlow\Core;

use Exception;
use Closure;
use ReflectionMethod;

/**
 * Advanced Hybrid Router
 */
class Router
{
    protected array $routes = [];
    protected array $currentGroup = [];
    protected Request $request;
    protected Response $response;

    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    /**
     * Group routes under a shared prefix and optional middleware.
     */
    public function group(array $attributes, \Closure $callback): void
    {
        $previousGroup = $this->currentGroup;
        
        // Merge prefix
        $prefix = trim($previousGroup['prefix'] ?? '', '/');
        if (isset($attributes['prefix'])) {
            $prefix = trim($prefix . '/' . trim($attributes['prefix'], '/'), '/');
        }
        $attributes['prefix'] = $prefix ? '/' . $prefix : '';

        // Merge middleware
        $groupMiddleware = (array)($attributes['middleware'] ?? []);
        $previousMiddleware = (array)($previousGroup['middleware'] ?? []);
        $attributes['middleware'] = array_unique(array_merge($previousMiddleware, $groupMiddleware));

        $this->currentGroup = array_merge($previousGroup, $attributes);
        $callback($this);
        $this->currentGroup = $previousGroup;
    }

    /**
     * Register a route.
     */
    public function add(string $method, string $path, mixed $callback): self
    {
        $prefix = $this->currentGroup['prefix'] ?? '';
        $path = '/' . trim($prefix . '/' . trim($path, '/'), '/');

        // Store route with current group's middleware
        $this->routes[strtolower($method)][$path] = [
            'callback'   => $callback,
            'middleware' => $this->currentGroup['middleware'] ?? []
        ];

        return $this;
    }

    /**
     * Fluent interface to add middleware to the LAST registered route
     */
    public function middleware(array|string $middleware): self
    {
        // Get the last added route info
        $method = array_key_last($this->routes);
        if ($method) {
            $path = array_key_last($this->routes[$method]);
            if ($path) {
                $this->routes[$method][$path]['middleware'] = array_unique(array_merge(
                    $this->routes[$method][$path]['middleware'],
                    (array)$middleware
                ));
            }
        }
        return $this;
    }

    public function get(string $path, mixed $callback): self
    {
        return $this->add('get', $path, $callback);
    }

    public function post(string $path, mixed $callback): self
    {
        return $this->add('post', $path, $callback);
    }

    public function put(string $path, mixed $callback): self
    {
        return $this->add('put', $path, $callback);
    }

    public function patch(string $path, mixed $callback): self
    {
        return $this->add('patch', $path, $callback);
    }

    public function delete(string $path, mixed $callback): self
    {
        return $this->add('delete', $path, $callback);
    }

    public function options(string $path, mixed $callback): self
    {
        return $this->add('options', $path, $callback);
    }

    public function any(string $path, mixed $callback): self
    {
        foreach (['get', 'post', 'put', 'patch', 'delete', 'options'] as $verb) {
            $this->add($verb, $path, $callback);
        }
        return $this;
    }

    /**
     * Register standard RESTful resource routes for an ERP entity or Controller.
     *
     * Generated Routes:
     * - GET    /{name}             => $controller@index
     * - GET    /{name}/create      => $controller@create
     * - POST   /{name}             => $controller@store
     * - GET    /{name}/{id}        => $controller@show
     * - GET    /{name}/{id}/edit   => $controller@edit
     * - PUT    /{name}/{id}        => $controller@update
     * - POST   /{name}/{id}/update => $controller@update (HTML form fallback)
     * - DELETE /{name}/{id}        => $controller@destroy
     * - POST   /{name}/{id}/delete => $controller@destroy (HTML form fallback)
     */
    public function resource(string $name, string $controller, array $options = []): self
    {
        $name = trim($name, '/');
        $only = isset($options['only']) ? (array) $options['only'] : null;
        $except = isset($options['except']) ? (array) $options['except'] : [];

        $actions = [
            'index'   => ['verb' => 'get',    'path' => "/$name",             'action' => 'index'],
            'create'  => ['verb' => 'get',    'path' => "/$name/create",      'action' => 'create'],
            'store'   => ['verb' => 'post',   'path' => "/$name",             'action' => 'store'],
            'show'    => ['verb' => 'get',    'path' => "/$name/{id}",        'action' => 'show'],
            'edit'    => ['verb' => 'get',    'path' => "/$name/{id}/edit",   'action' => 'edit'],
            'update'  => ['verb' => 'put',    'path' => "/$name/{id}",        'action' => 'update'],
            'destroy' => ['verb' => 'delete', 'path' => "/$name/{id}",        'action' => 'destroy'],
        ];

        foreach ($actions as $key => $action) {
            if ($only !== null && !in_array($key, $only, true)) {
                continue;
            }
            if (in_array($key, $except, true)) {
                continue;
            }

            $this->add($action['verb'], $action['path'], $controller . '@' . $action['action']);

            // For HTML form compatibility where PUT/DELETE might use direct POST URLs
            if ($key === 'update') {
                $this->add('post', "/$name/{id}/update", $controller . '@update');
            } elseif ($key === 'destroy') {
                $this->add('post', "/$name/{id}/delete", $controller . '@destroy');
            }
        }

        return $this;
    }

    /**
     * Group routes under an ERP Module prefix with automatic module metadata
     */
    public function module(string $name, \Closure $callback, array $attributes = []): void
    {
        $prefix = strtolower(trim($name, '/'));
        $attributes['prefix'] = $attributes['prefix'] ?? $prefix;
        $attributes['module'] = $name;

        $this->group($attributes, $callback);
    }

    /**
     * Auto-discover and register all ERP module routes from app/Modules
     */
    public function loadModules(?string $path = null): self
    {
        $baseDir = $path ?: (defined('BASE_PATH') ? BASE_PATH . '/app/Modules' : dirname(__DIR__, 2) . '/app/Modules');

        if (!is_dir($baseDir)) {
            return $this;
        }

        $modules = glob($baseDir . '/*', GLOB_ONLYDIR);
        if ($modules === false) {
            return $this;
        }

        foreach ($modules as $moduleDir) {
            $routeFile = $moduleDir . '/routes.php';
            if (file_exists($routeFile)) {
                $moduleName = basename($moduleDir);
                $router = $this;
                require $routeFile;
            }
        }

        return $this;
    }

    // ============================================================
    // Unified Master Route Loader:
    // 0. Checks if compiled route cache exists in storage/cache/routes.php (0.01ms ultra-fast)
    // 1. Loads routes from routes/ (web.php, admin.php, api.php, etc.) if not cached
    // 2. Loads all ERP business modules from app/Modules (subdirectories)
    // ============================================================
    public function loadAllRoutes(): self
    {
        $baseDir = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        $cacheFile = $this->getCachePath($baseDir);

        // 0. Ultra-Fast Path: If compiled route cache exists, load directly in 0.01ms!
        if (file_exists($cacheFile)) {
            $cached = @require $cacheFile;
            if (is_array($cached)) {
                $this->routes = $cached;
                return $this;
            }
        }

        // 1. Auto-load all routes from routes/*.php dynamically
        $routesDir = $baseDir . '/routes';
        if (is_dir($routesDir)) {
            $files = glob($routesDir . '/*.php');
            if ($files !== false) {
                // Prioritize web.php first, then other files alphabetically
                usort($files, function ($a, $b) {
                    if (basename($a) === 'web.php') return -1;
                    if (basename($b) === 'web.php') return 1;
                    return strcmp($a, $b);
                });

                foreach ($files as $fullPath) {
                    $router = $this;
                    require $fullPath;
                }
            }
        }

        // 2. Load all ERP modules from app/Modules
        $this->loadModules($baseDir . '/app/Modules');

        return $this;
    }

    /**
     * Get route cache file path
     */
    public function getCachePath(?string $baseDir = null): string
    {
        $base = $baseDir ?: (defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2));
        return $base . '/storage/cache/routes.php';
    }

    /**
     * Compile and cache all registered routes to storage/cache/routes.php
     */
    public function cacheRoutes(?string $path = null): bool
    {
        $cacheFile = $path ?: $this->getCachePath();

        // Check for Closures (Closures cannot be safely serialized to static PHP cache)
        foreach ($this->routes as $method => $paths) {
            foreach ($paths as $routePath => $info) {
                if ($info['callback'] instanceof \Closure) {
                    throw new \RuntimeException("Unable to cache route [$method $routePath]: Route uses an anonymous Closure. Please use 'Controller@method' notation for route caching.");
                }
            }
        }

        $dir = dirname($cacheFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $content = "<?php\n\n";
        $content .= "// ============================================================\n";
        $content .= "//  NovaFlow Compiled Route Cache — " . date('Y-m-d H:i:s') . "\n";
        $content .= "//  Auto-generated by `php cli.php route:cache`\n";
        $content .= "//  Do not modify this file manually. Run `php cli.php route:clear`\n";
        $content .= "// ============================================================\n\n";
        $content .= "return " . var_export($this->routes, true) . ";\n";

        return file_put_contents($cacheFile, $content, LOCK_EX) !== false;
    }

    /**
     * Clear compiled route cache
     */
    public function clearRouteCache(?string $path = null): bool
    {
        $cacheFile = $path ?: $this->getCachePath();
        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }
        return true;
    }

    /**
     * Check if route cache file currently exists
     */
    public function isCached(?string $path = null): bool
    {
        $cacheFile = $path ?: $this->getCachePath();
        return file_exists($cacheFile);
    }

    /**
     * Resolve the current request.
     */
    public function resolve(): void
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();
        
        // 0. Run Global Middlewares
        if (!$this->runMiddlewares(\App\Middleware\Kernel::$global)) {
            return;
        }

        // 1. First, check for explicit routes
        foreach ($this->routes[$method] ?? [] as $routePath => $routeInfo) {
            $callback = $routeInfo['callback'];
            $middlewares = $routeInfo['middleware'];

            // Support simple parameters {id}
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $routePath);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                
                // Execute Middlewares
                if (!$this->runMiddlewares($middlewares)) {
                    return; // Middleware stopped the request
                }

                $this->dispatchExplicit($callback, $matches);
                return;
            }
        }

        // 2. If no explicit route, fallback to auto-routing
        $this->dispatchAuto($path);
    }

    /**
     * Run a chain of middlewares
     */
    protected function runMiddlewares(array $middlewares): bool
    {
        foreach ($middlewares as $mw) {
            $args = [];
            
            // Handle middleware with parameters (e.g. role:admin)
            if (str_contains($mw, ':')) {
                [$mw, $argString] = explode(':', $mw);
                $args = explode(',', $argString);
            }

            // Resolve Alias via Kernel
            $mwClass = \App\Middleware\Kernel::$aliases[$mw] ?? $mw;

            // Fallback for namespacing if not fully qualified
            if (!class_exists($mwClass) && !str_contains($mwClass, '\\')) {
                $mwClass = 'App\\Middleware\\' . $mwClass;
            }

            if (!class_exists($mwClass)) {
                throw new \Exception("Middleware class [$mwClass] not found.", 500);
            }

            $mwInstance = Container::getInstance()->make($mwClass);
            if (!$mwInstance instanceof Middleware) {
                throw new \Exception("Class [$mwClass] must implement Middleware interface.", 500);
            }

            // Execute handle
            if ($mwInstance->handle($this->request, $this->response, $args) === false) {
                return false; // Stopped
            }
        }

        return true;
    }

    /**
     * Dispatch an explicit route (defined in config/routes.php)
     */
    protected function dispatchExplicit(mixed $callback, array $params): void
    {
        if (is_callable($callback)) {
            $result = call_user_func_array($callback, array_merge([$this->request, $this->response], $params));
            if (str_starts_with($this->request->getPath(), '/api')) {
                if (ob_get_length()) ob_clean();
                header('Content-Type: application/json; charset=utf-8');
                if (is_array($result) || is_object($result)) {
                    echo json_encode($result, JSON_UNESCAPED_UNICODE);
                    return;
                }
            }
            if (is_string($result) || is_numeric($result)) {
                echo $result;
            }
            return;
        }

        if (is_string($callback) && str_contains($callback, '@')) {
            [$controllerName, $method] = explode('@', $callback);
            $this->executeController($controllerName, $method, $params);
            return;
        }

        throw new \Exception("Invalid route callback", 500);
    }

    /**
     * Dispatch using the old auto-routing logic
     */
    protected function dispatchAuto(string $path): void
    {
        $segments = explode('/', trim($path, '/'));

        $controllerName = ucfirst($segments[0] ?: 'dashboard') . 'Controller';
        $method = $segments[1] ?? 'index';
        $params = array_slice($segments, 2);

        $this->executeController($controllerName, $method, $params, true);
    }

    /**
     * Load and execute the controller method with Dependency Injection
     */
    protected function executeController(string $controllerName, string $method, array $urlParams, bool $isAutoRoute = false): void
    {
        if (!class_exists($controllerName)) {
            $candidates = [
                $controllerName,
                'App\\Controllers\\' . ltrim($controllerName, '\\'),
            ];

            // Support Module controllers (e.g. "Sales\InvoiceController" => App\Modules\Sales\Controllers\InvoiceController)
            if (strpos($controllerName, '\\') !== false) {
                [$mod, $ctrl] = explode('\\', ltrim($controllerName, '\\'), 2);
                $candidates[] = "App\\Modules\\{$mod}\\Controllers\\{$ctrl}";
            }

            foreach ($candidates as $candidate) {
                if (class_exists($candidate)) {
                    $controllerName = $candidate;
                    break;
                }
            }
        }

        if (!class_exists($controllerName)) {
            $this->notFound();
            return;
        }

        // 1. Resolve Controller from Container
        $container = Container::getInstance();
        $controller = $container->make($controllerName);

        // 2. Execute Controller-level Middleware
        if (method_exists($controller, 'getMiddlewares')) {
            $controllerMiddlewares = $controller->getMiddlewares();
            if (!$this->runMiddlewares($controllerMiddlewares)) {
                return;
            }
        }

        // Global Auth Guard (Skip for APIs)
        if (!is_subclass_of($controllerName, ApiController::class) && 
            $controllerName !== 'App\\Controllers\\AuthController' && 
            method_exists($controller, 'checkAuth')) {
            $controller->checkAuth();
        }

        if (!method_exists($controller, $method)) {
            $this->notFound();
            return;
        }

        // 3. Reflection for Method Injection
        $reflection = new \ReflectionMethod($controller, $method);
        $parameters = $reflection->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            
            if ($type && !$type->isBuiltin()) {
                // It's a class/interface, resolve from container
                $className = $type->getName();
                
                // Special case for Request and Response (Inject current ones)
                if ($className === Request::class || is_subclass_of($className, Request::class)) {
                    $dependencies[] = $this->request;
                } elseif ($className === Response::class || is_subclass_of($className, Response::class)) {
                    $dependencies[] = $this->response;
                } else {
                    $dependencies[] = $container->make($className);
                }
            } else {
                // It's a primitive type, take from URL params
                if (!empty($urlParams)) {
                    $dependencies[] = array_shift($urlParams);
                } elseif ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    $dependencies[] = null;
                }
            }
        }

        // 4. Call the method with injected dependencies + remaining URL params
        $result = $reflection->invokeArgs($controller, array_merge($dependencies, $urlParams));
        
        if (is_string($result) || is_numeric($result)) {
            echo $result;
        }
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    private function notFound(): void
    {
        $this->response->setStatusCode(404);
        
        // If API request, return JSON
        if (str_starts_with($this->request->getPath(), '/api')) {
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'API route not found.']);
            exit;
        }

        $file = VIEW_PATH . '/errors/404.php';
        if (file_exists($file)) {
            require_once $file;
        } else {
            echo "404 Not Found";
        }
        exit;
    }
}
