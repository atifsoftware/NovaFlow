<?php

namespace App\Controllers;

use NovaFlow\Core\Controller;

/**
 * HomeController
 * Landing Page for the project
 */
class HomeController extends Controller
{
    public function checkAuth(): void {} // Public

    public function index(): void
    {
        $this->view('welcome', [
            'title' => 'স্বাগতম — NovaFlow PHP Framework'
        ], 'main');
    }
}
