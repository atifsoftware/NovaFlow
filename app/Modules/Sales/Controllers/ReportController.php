<?php

namespace App\Modules\Sales\Controllers;

use NovaFlow\Core\Controller;

class ReportController extends Controller
{
    public function summary(): void
    {
        $this->view('Sales::invoices.index', ['title' => 'Sales Summary Report'], 'main');
    }

    public function byCustomer(): void
    {
        $this->view('Sales::invoices.index', ['title' => 'Sales by Customer Report'], 'main');
    }
}
