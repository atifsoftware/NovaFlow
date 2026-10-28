<?php

namespace App\Controllers;

use NovaFlow\Core\Controller;
use NovaFlow\Core\DB;

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
        $this->view('admin.dashboard.index', [
            'title' => 'ড্যাশবোর্ড — NovaFlow',
            'stats' => $this->getStats()
        ], 'admin');
    }

    private function getStats(): array
    {
        // Placeholder stats for a fresh framework
        return [
            'users' => DB::fetchValue("SELECT COUNT(*) FROM users"),
            'online' => 1,
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'PHP ' . PHP_VERSION . ' CLI/Server',
            'php_version' => PHP_VERSION
        ];
    }
}
