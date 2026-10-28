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
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }
            $_SESSION['user_id']   = $user->id;
            $_SESSION['user_name'] = $user->name;
            $_SESSION['role']      = $user->role ?? 'user';
            $_SESSION['user_role'] = $user->role ?? 'user';
            
            flash('success', 'লগইন সফল হয়েছে!');
            $this->redirect('/admin/dashboard');
        } else {
            flash('error', 'ভুল ইমেইল বা পাসওয়ার্ড।');
            $this->redirect('/login');
        }
    }

    /**
     * Show registration form
     */
    public function register(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/admin/dashboard');
        }

        $this->view('auth.register', [
            'title' => 'নিবন্ধন — NovaFlow'
        ], 'auth');
    }

    /**
     * Handle user registration submission
     */
    public function postRegister(): void
    {
        $name = trim((string) $this->post('name'));
        $email = trim((string) $this->post('email'));
        $password = (string) $this->post('password');
        $passwordConfirmation = (string) $this->post('password_confirmation');

        if (empty($name) || empty($email) || empty($password)) {
            flash('error', 'সবগুলো প্রয়োজনীয় তথ্য প্রদান করুন।');
            $this->redirect('/register');
        }

        if ($password !== $passwordConfirmation) {
            flash('error', 'পাসওয়ার্ড কনফার্মেশন মিলছে না।');
            $this->redirect('/register');
        }

        if (strlen($password) < 8) {
            flash('error', 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে।');
            $this->redirect('/register');
        }

        // Check if email already exists
        $existing = UserModel::findByEmail($email);
        if ($existing) {
            flash('error', 'এই ইমেইলটি ইতিমধ্যেই নিবন্ধিত রয়েছে।');
            $this->redirect('/register');
        }

        $user = new UserModel();
        $user->name = $name;
        $user->email = $email;
        $user->password = \NovaFlow\Core\Security::hashPassword($password);
        $user->role = 'user';
        $user->status = 'active';
        $user->save();

        flash('success', 'নিবন্ধন সফল হয়েছে! অনুগ্রহ করে লগইন করুন।');
        $this->redirect('/login');
    }

    /**
     * Handle logout
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        $this->redirect('/login');
    }

    private function isLoggedIn(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }
}
