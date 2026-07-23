<?php

namespace App\Controllers;

use App\Services\AuthService;
use NovaFlow\Core\Controller;
use NovaFlow\Core\Flash;

/**
 * AuthController
 * Manages admin login/logout
 */
class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
        parent::__construct();
    }

    public function checkAuth(): void {}

    /**
     * Show login form
     */
    public function login(): void
    {
        if ($this->authService->isLoggedIn()) {
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
        $remember = $this->post('remember') === 'on';

        if (empty($email) || empty($password)) {
            Flash::error('সবগুলো ঘর পূরণ করুন।');
            $this->redirect('/login');
        }

        $result = $this->authService->login($email, $password, $remember);

        if ($result['success']) {
            Flash::success('লগইন সফল হয়েছে!');
            $this->redirect('/admin/dashboard');
        } else {
            Flash::error($result['message']);
            $this->redirect('/login');
        }
    }

    /**
     * Handle logout
     */
    public function logout(): void
    {
        $this->authService->logout();
        $this->redirect('/login');
    }

    /**
     * Show registration form
     */
    public function register(): void
    {
        $this->view('auth.register', [
            'title' => 'রেজিস্টার — NovaFlow'
        ], 'auth');
    }

    /**
     * Handle registration
     */
    public function postRegister(): void
    {
        $data = [
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'password' => $this->post('password'),
            'confirm_password' => $this->post('confirm_password')
        ];

        if ($data['password'] !== $data['confirm_password']) {
            Flash::error('পাসওয়ার্ড মিলছে না।');
            $this->redirect('/register');
        }

        $result = $this->authService->register($data);

        if ($result['success']) {
            Flash::success('রেজিস্ট্রেশন সফল! অনুগ্রহ করে লগইন করুন।');
            $this->redirect('/login');
        } else {
            Flash::error($result['message']);
            $this->redirect('/register');
        }
    }
}