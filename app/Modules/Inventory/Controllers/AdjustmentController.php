<?php

namespace App\Modules\Inventory\Controllers;

use NovaFlow\Core\Controller;

class AdjustmentController extends Controller
{
    public function index(): void
    {
        $this->view('Inventory::items.index', ['title' => 'Stock Adjustments'], 'main');
    }

    public function create(): void
    {
        $this->view('Inventory::items.create', ['title' => 'New Stock Adjustment'], 'main');
    }

    public function store(): void
    {
        flash('success', 'Adjustment recorded successfully!');
        $this->redirect('/inventory/adjustments');
    }

    public function show($id): void {}
    public function edit($id): void {}
    public function update($id): void {}
    public function destroy($id): void {}
}
