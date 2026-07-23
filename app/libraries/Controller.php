<?php

namespace NovaFlow\Core;

/**
 * Base Controller
 */
abstract class Controller
{
    protected array $middlewares = [];

    public function __construct()
    {
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

    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
        exit;
    }

    protected function json(mixed $data, int $status = 200): void
    {
        if (ob_get_length()) ob_clean(); // Clear any previous output or warnings
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
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

    protected function input(string $key, mixed $default = null): mixed
    {
        return Request::input($key, $default);
    }

    protected function all(): array
    {
        return Request::all();
    }

    protected function file(string $key): ?array
    {
        return Request::file($key);
    }

    protected function has(string $key): bool
    {
        return !empty(Request::input($key));
    }

    public function checkAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
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
     * Check if user has permission for a specific module
     */
    public function hasPermission(string $module): bool
    {
        if ($this->isAdmin()) return true;
        
        $permissions = $_SESSION['permissions'] ?? [];
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?: [];
        }
        
        return isset($permissions[$module]) && $permissions[$module] == true;
    }

    /**
     * Enforce permission and redirect if unauthorized
     */
    protected function checkPermission(string $module): void
    {
        if (!$this->hasPermission($module)) {
            $this->setFlash('danger', 'আপনার এই মডিউলটি ব্যবহার করার অনুমতি নেই।');
            $this->redirect('dashboard/index');
        }
    }

    /**
     * ইমপ্লিমেন্টেড ভ্যালিডেশন হেল্পার
     */
    protected function validate(array $rules, array $messages = []): Validator
    {
        $data = array_merge($_GET, $_POST);
        $validator = Validator::make($data, $rules, $messages);
        
        if ($validator->hasErrors()) {
            $errors = $validator->errors();
            $firstField = array_key_first($errors);
            $error = $validator->firstError($firstField);
            
            $this->setFlash('danger', $error);
            
            $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/dashboard/index';
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
