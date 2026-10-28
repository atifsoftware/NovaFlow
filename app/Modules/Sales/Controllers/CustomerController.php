<?php

namespace App\Modules\Sales\Controllers;

use NovaFlow\Core\Controller;

class CustomerController extends Controller
{
    public function index(): void
    {
        $this->view('Sales::invoices.index', ['title' => 'Customer Management'], 'main');
    }

    public function create(): void
    {
        $this->view('Sales::invoices.create', ['title' => 'Add Customer'], 'main');
    }

    public function store(): void
    {
        flash('success', 'Customer saved successfully!');
        $this->redirect('/sales/customers');
    }

    public function show($id): void {}
    public function edit($id): void {}
    public function update($id): void {}
    public function destroy($id): void {}
}
