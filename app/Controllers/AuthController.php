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
        $data = [
            'email' => $this->post('email'),
            'password' => $this->post('password')
        ];

        // Validation using Validator class
        $validator = \NovaFlow\Core\Validator::make($data, [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ], [], [
            'email' => 'ইমেইল',
            'password' => 'পাসওয়ার্ড'
        ]);

        if (!$validator->validate()) {
            Flash::error($validator->firstError(array_key_first($validator->errors())));
            $this->redirect('/login');
        }

        $remember = $this->post('remember') === 'on';
        $result = $this->authService->login($data['email'], $data['password'], $remember);

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
            'password_confirmation' => $this->post('confirm_password')
        ];

        // Validation using Validator class
        $validator = \NovaFlow\Core\Validator::make($data, [
            'name' => 'required|min:3|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed'
        ], [], [
            'name' => 'নাম',
            'email' => 'ইমেইল',
            'password' => 'পাসওয়ার্ড'
        ]);

        if (!$validator->validate()) {
            Flash::error($validator->firstError(array_key_first($validator->errors())));
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