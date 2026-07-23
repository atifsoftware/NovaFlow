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

    /**
     * Resolve the current request.
     */
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
            }
            echo $result;
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
            // If it's a namespaced controller under App\Controllers, we ensure it has the full path
            if (strpos($controllerName, 'App\\Controllers\\') !== 0) {
                $controllerName = 'App\\Controllers\\' . ltrim($controllerName, '\\');
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

        // 2. Reflection for Method Injection
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

        // 3. Call the method with injected dependencies + remaining URL params
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
