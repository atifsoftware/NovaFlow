<?php

namespace NovaFlow\Core;

/**
 * Base Controller
 */
abstract class Controller
{
    /**
     * Set this to true in child controllers to skip CSRF validation for specific cases
     */
    protected bool $skipCsrf = false;
    protected array $middlewares = [];

    public function __construct()
    {
        // Automatically validate CSRF for all POST requests
        if (!$this->skipCsrf && Request::isPost()) {
            $this->validateCsrf();
        }
    }

    /**
     * Register middleware for this controller
     */
    protected function middleware(string|array $middleware): void
    {
        if (is_string($middleware)) {
            $this->middlewares[] = $middleware;
        } else {
            $this->middlewares = array_merge($this->middlewares, $middleware);
        }
    }

    /**
     * Get registered middlewares
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewService = Container::getInstance()->make(View::class);
        echo $viewService->render($view, $data, $layout);
    }

    /**
     * Render an Inertia.js component with props
     */
    protected function inertia(string $component, array $props = []): void
    {
        Inertia::render($component, $props);
    }

    /**
     * Perform an external or full page redirect under the Inertia protocol
     */
    protected function inertiaLocation(string $url): void
    {
        Inertia::location($url);
    }

    protected function redirect(string $path): void
    {
        $base = defined('BASE_URL') ? BASE_URL : '';
        header('Location: ' . rtrim($base, '/') . '/' . ltrim($path, '/'));
        if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
            exit;
        }
    }

    protected function json(mixed $data, int $status = 200): void
    {
        if (ob_get_length()) ob_clean(); // Clear any previous output or warnings
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        exit;
    }

    protected function isPost(): bool
    {
        return Request::isPost();
    }

    protected function post(?string $key = null, mixed $default = null): mixed
    {
        return Request::post($key, $default);
    }


    protected function get(?string $key = null, mixed $default = null): mixed
    {
        return Request::get($key, $default);
    }

    protected function setFlash(string $type, string $message): void
    {
        // ‘danger’ কে ‘error’ এ ম্যাপ করো Flash::error() এর সাথে সামঞ্জস্য রাখতে
        $flashType = ($type === 'danger') ? 'error' : $type;
        if ($flashType === 'error') {
            Flash::error($message);
        } else {
            Flash::success($message);
        }
    }

    protected function getFlash(): void
    {
        if (function_exists('flash')) {
            flash('success');
            flash('error');
        }
    }

    /**
     * CSRF চেক করার মেথড
     */
    protected function validateCsrf(): void
    {
        $token = $this->post('_token');
        if (!$token) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        }
        
        if (!Security::validateCSRFToken($token)) {
            // Log the failure for security monitoring
            error_log("CSRF validation failed for user ID: " . $this->getUserId() . " from IP: " . Request::ip());
            
            if (Request::isAjax()) {
                $this->json(['error' => 'Invalid CSRF Token. Please refresh the page.'], 403);
            }
            
            die('<div style="font-family:sans-serif;padding:2rem;color:red;text-align:center;">
                <h2>নিরাপত্তা ত্রুটি (CSRF Error)</h2>
                <p>পেইজটি রিলোড দিয়ে আবার চেষ্টা করুন।</p>
                <a href="javascript:location.reload()" style="color:blue;">Reload Page</a>
            </div>');
        }
    }

    public function checkAuth(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    public function checkAdmin(): void
    {
        $this->checkAuth();
        if ($_SESSION['role'] !== 'admin') {
            $this->setFlash('danger', 'আপনার এই পেজে প্রবেশের অনুমতি নেই।');
            $this->redirect('dashboard/index');
        }
    }

    protected function getUserId(): int { return $_SESSION['user_id'] ?? 0; }
    protected function getUserRole(): string { return $_SESSION['role'] ?? 'viewer'; }
    protected function isAdmin(): bool { return $this->getUserRole() === 'admin'; }
    protected function isManager(): bool { return $this->getUserRole() === 'manager'; }

    protected function getAssignedShopIds(): array { 
        return $_SESSION['assigned_shop_ids'] ?? []; 
    }

    public function hasShopAccess(int $shopId): bool {
        if ($this->isAdmin()) return true;
        return in_array($shopId, $this->getAssignedShopIds());
    }

    /**
     * Check if user has permission for a specific module or action (via Dynamic RBAC)
     */
    public function hasPermission(string $module): bool
    {
        return RBAC::hasPermission($this->getUserId(), $module);
    }

    /**
     * Alias for hasPermission
     */
    public function can(string $permission): bool
    {
        return RBAC::can($permission, $this->getUserId());
    }

    /**
     * Check if user has given role(s)
     */
    public function hasRole(string|array $roles): bool
    {
        return RBAC::hasRole($this->getUserId(), $roles);
    }

    /**
     * Authorize user for a permission, or abort/redirect with flash message
     */
    protected function authorize(string $permission, string $message = 'আপনার এই পেজে প্রবেশের অনুমতি নেই।'): void
    {
        if (!$this->can($permission)) {
            if (Request::wantsJson()) {
                $this->json(['success' => false, 'message' => 'Forbidden: ' . $message], 403);
            }
            $this->setFlash('danger', $message);
            $this->redirect('dashboard/index');
        }
    }

    /**
     * Enforce permission and redirect if unauthorized
     */
    protected function checkPermission(string $module): void
    {
        $this->authorize($module, 'আপনার এই মডিউলটি ব্যবহার করার অনুমতি নেই।');
    }

    /**
     * ইমপ্লিমেন্টেড ভ্যালিডেশন হেল্পার
     */
    protected function validate(array $rules, array $messages = []): Validator
    {
        $data = Request::all();
        $validator = Validator::make($data, $rules, $messages);
        
        if ($validator->hasErrors()) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $errors = $validator->errors();
            $_SESSION['_old_input'] = $data;
            $_SESSION['_errors'] = $errors;
            $_SESSION['_flash_errors'] = $errors;

            $firstField = array_key_first($errors);
            $error = $validator->firstError($firstField);
            
            $this->setFlash('danger', $error);
            
            $referer = Request::safeReferer(BASE_URL . '/dashboard/index');
            header('Location: ' . $referer);
            exit;
        }
        
        return $validator;
    }

    // ফরম্যাট হেল্পার
    protected function numberFormat(float $number): string
    {
        return CURRENCY . ' ' . number_format($number, 2, '.', ',');
    }
}
