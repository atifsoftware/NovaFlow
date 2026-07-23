<?php

namespace App\Controllers;

use App\Models\UserModel;
use NovaFlow\Core\Controller;

/**
 * AdminDashboardController
 * Primary administrative overview
 */
class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): void
    {
        $this->view('admin/dashboard/index', [
            'title' => 'ড্যাশবোর্ড — NovaFlow',
            'stats' => $this->getStats()
        ], 'admin');
    }

    private function getStats(): array
    {
        $users = UserModel::query()->count() ?? 0;
        $activeUsers = UserModel::query()->where('status', 'active')->count() ?? 0;

        return [
            'users' => $users,
            'active_users' => $activeUsers,
            'online' => 1,
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'php_version' => PHP_VERSION,
            'app_version' => APP_VERSION ?? '1.0.0'
        ];
    }
}
