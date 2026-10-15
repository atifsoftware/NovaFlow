<?php

namespace App\Modules\Sales\Controllers;

use NovaFlow\Core\Controller;

class InvoiceController extends Controller
{
    public function index(): void
    {
        // Render module view wrapped in global layout 'main'
        $this->view('Sales::invoices.index', [
            'title' => 'Invoices — Sales Module'
        ], 'main');
    }
}
