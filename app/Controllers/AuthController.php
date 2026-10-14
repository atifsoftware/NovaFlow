<?php

namespace App\Controllers;

use App\Models\UserModel;
use NovaFlow\Core\Controller;

/**
 * AuthController
 * Manages admin login/logout
 */
class AuthController extends Controller
{
    public function checkAuth(): void {} // Public accessibility

    /**
     * Show login form
     */
    public function login(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/admin/dashboard');
        }

        $this->view('auth.login', [
            'title' => 'লগইন — NovaFlow'
        ], 'auth');
    }

    /**
     * Handle login submission
     */
    public function postLogin(): void
    {
        $email    = $this->post('email');
        $password = $this->post('password');

        if (empty($email) || empty($password)) {
            flash('error', 'সবগুলো ঘর পূরণ করুন।');
            $this->redirect('/login');
        }

        $user = UserModel::authenticate($email, $password);

        if ($user) {
            $_SESSION['user_id']   = $user->id;
            $_SESSION['user_name'] = $user->name;
            $_SESSION['user_role'] = $user->role ?? 'user';
            
            flash('success', 'লগইন সফল হয়েছে!');
            $this->redirect('/admin/dashboard');
        } else {
            flash('error', 'ভুল ইমেইল বা পাসওয়ার্ড।');
            $this->redirect('/login');
        }
    }

    /**
     * Handle logout
     */
    public function logout(): void
    {
        session_unset();
        session_destroy();
        $this->redirect('/login');
    }

    private function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }
}
