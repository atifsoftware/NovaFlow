<?php

namespace App\Modules\Sales\Controllers;

use NovaFlow\Core\Controller;

class QuotationController extends Controller
{
    public function index(): void
    {
        $this->view('Sales::invoices.index', ['title' => 'Sales Quotations'], 'main');
    }

    public function create(): void
    {
        $this->view('Sales::invoices.create', ['title' => 'Create Quotation'], 'main');
    }

    public function store(): void
    {
        flash('success', 'Quotation created successfully!');
        $this->redirect('/sales/quotations');
    }

    public function show($id): void {}
    public function edit($id): void {}
    public function update($id): void {}
    public function destroy($id): void {}
}
