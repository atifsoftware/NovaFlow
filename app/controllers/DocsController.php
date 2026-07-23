<?php

namespace App\Controllers;

use NovaFlow\Core\Controller;

/**
 * DocsController
 * Handles the bilingual documentation interface
 */
class DocsController extends Controller
{
    public function checkAuth(): void {} // Documentation is public

    /**
     * Show documentation main page
     */
    public function index(): void
    {
        $this->view('docs.index', [
            'title' => 'Documentation — NovaFlow PHP Framework'
        ], 'main');
    }
}
