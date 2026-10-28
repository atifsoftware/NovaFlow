<?php

namespace App\Modules\Accounts\Controllers;

use NovaFlow\Core\Controller;

class ChartOfAccountsController extends Controller
{
    public function index(): void
    {
        $this->view('Accounts::chart_of_accounts.index', [
            'title' => 'Chart of Accounts — Accounts & Finance'
        ], 'main');
    }

    public function create(): void
    {
        $this->view('Accounts::chart_of_accounts.create', ['title' => 'Create Account Head'], 'main');
    }

    public function store(): void
    {
        flash('success', 'Account created successfully!');
        $this->redirect('/accounts/chart-of-accounts');
    }

    public function show($id): void
    {
        $this->view('Accounts::chart_of_accounts.show', ['id' => $id, 'title' => 'Account Details'], 'main');
    }

    public function edit($id): void
    {
        $this->view('Accounts::chart_of_accounts.edit', ['id' => $id, 'title' => 'Edit Account'], 'main');
    }

    public function update($id): void
    {
        flash('success', 'Account updated successfully!');
        $this->redirect('/accounts/chart-of-accounts');
    }

    public function destroy($id): void
    {
        flash('success', 'Account deleted successfully!');
        $this->redirect('/accounts/chart-of-accounts');
    }
}
