<?php

namespace App\Controllers;

use NovaFlow\Core\Controller;

/**
 * CustomerAuthController
 * Manages customer portal authentication with session fixation protection
 */
class CustomerAuthController extends Controller
{
    public function checkAuth(): void {}

    /**
     * Show customer login form
     */
    public function login(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/customer/dashboard');
        }

        $this->view('customer.login', [
            'title' => 'Customer Login — NovaFlow'
        ], 'auth');
    }

    /**
     * Handle customer login submission
     */
    public function postLogin(): void
    {
        $email    = (string)$this->post('email');
        $password = (string)$this->post('password');

        if (empty($email) || empty($password)) {
            flash('error', 'Please fill in all fields.');
            $this->redirect('/customer/login');
        }

        // Authenticate customer
        $customer = null;
        if (class_exists('App\\Models\\CustomerModel') && method_exists('App\\Models\\CustomerModel', 'authenticate')) {
            $customer = \App\Models\CustomerModel::authenticate($email, $password);
        } elseif (class_exists('App\\Models\\UserModel')) {
            $customer = \App\Models\UserModel::authenticate($email, $password);
        }

        if ($customer) {
            $this->loginCustomer($customer);
            flash('success', 'Login successful!');
            $this->redirect('/customer/dashboard');
        } else {
            flash('error', 'Invalid email or password.');
            $this->redirect('/customer/login');
        }
    }

    /**
     * Set authenticated customer session with session fixation protection
     */
    public function loginCustomer(object|array $customer): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Session fixation protection: regenerate session ID before setting credentials
        session_regenerate_id(true);

        $id = is_object($customer) ? ($customer->id ?? null) : ($customer['id'] ?? null);
        $name = is_object($customer) ? ($customer->name ?? null) : ($customer['name'] ?? null);
        $email = is_object($customer) ? ($customer->email ?? null) : ($customer['email'] ?? null);

        $_SESSION['customer_id'] = $id;
        $_SESSION['customer_name'] = $name;
        $_SESSION['customer_email'] = $email;
        $_SESSION['customer_logged_in'] = true;
    }

    /**
     * Handle logout: regenerate session ID and clear all customer session variables
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Session fixation protection: regenerate session ID on logout
        session_regenerate_id(true);

        // Clear all customer session variables
        unset($_SESSION['customer_id']);
        unset($_SESSION['customer_name']);
        unset($_SESSION['customer_email']);
        unset($_SESSION['customer_logged_in']);

        flash('success', 'You have been logged out.');
        $this->redirect('/customer/login');
    }

    /**
     * Check if customer is currently logged in
     */
    public function isLoggedIn(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return !empty($_SESSION['customer_id']) && !empty($_SESSION['customer_logged_in']);
    }
}
