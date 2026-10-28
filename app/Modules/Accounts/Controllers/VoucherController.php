<?php

namespace App\Modules\Accounts\Controllers;

use NovaFlow\Core\Controller;

class VoucherController extends Controller
{
    public function index(): void
    {
        $this->view('Accounts::vouchers.index', [
            'title' => 'Vouchers — Accounts & Finance'
        ], 'main');
    }

    public function create(): void
    {
        $this->view('Accounts::vouchers.create', ['title' => 'Create Voucher'], 'main');
    }

    public function store(): void
    {
        flash('success', 'Voucher saved successfully!');
        $this->redirect('/accounts/vouchers');
    }

    public function show($id): void
    {
        $this->view('Accounts::vouchers.show', ['id' => $id, 'title' => 'Voucher Details'], 'main');
    }

    public function edit($id): void
    {
        $this->view('Accounts::vouchers.edit', ['id' => $id, 'title' => 'Edit Voucher'], 'main');
    }

    public function update($id): void
    {
        flash('success', 'Voucher updated successfully!');
        $this->redirect('/accounts/vouchers');
    }

    public function destroy($id): void
    {
        flash('success', 'Voucher deleted successfully!');
        $this->redirect('/accounts/vouchers');
    }
}
