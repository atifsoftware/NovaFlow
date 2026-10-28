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

    public function create(): void
    {
        $this->view('Sales::invoices.create', ['title' => 'Create Invoice — Sales'], 'main');
    }

    public function store(): void
    {
        flash('success', 'Invoice created successfully!');
        $this->redirect('/sales/invoices');
    }

    public function show($id): void
    {
        $this->view('Sales::invoices.show', ['id' => $id, 'title' => 'Invoice Details'], 'main');
    }

    public function edit($id): void
    {
        $this->view('Sales::invoices.edit', ['id' => $id, 'title' => 'Edit Invoice'], 'main');
    }

    public function update($id): void
    {
        flash('success', 'Invoice updated successfully!');
        $this->redirect('/sales/invoices');
    }

    public function destroy($id): void
    {
        flash('success', 'Invoice deleted successfully!');
        $this->redirect('/sales/invoices');
    }
}
