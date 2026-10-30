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

    public function inertiaDemo(): void
    {
        $this->inertia('Welcome', [
            'title'       => 'NovaFlow PHP + Inertia.js',
            'description' => 'PHP MVC Controller communicates directly with React 19 without any REST API!',
            'version'     => \NovaFlow\Core\Inertia::getVersion(),
            'auth'        => [
                'user' => 'PHP NovaFlow Developer'
            ],
            'appName'     => 'NovaFlow PHP'
        ]);
    }
}
