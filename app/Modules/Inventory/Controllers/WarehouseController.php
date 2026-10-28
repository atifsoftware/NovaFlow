<?php

namespace App\Modules\Inventory\Controllers;

use NovaFlow\Core\Controller;

class WarehouseController extends Controller
{
    public function index(): void
    {
        $this->view('Inventory::items.index', ['title' => 'Warehouses'], 'main');
    }

    public function create(): void
    {
        $this->view('Inventory::items.create', ['title' => 'Create Warehouse'], 'main');
    }

    public function store(): void
    {
        flash('success', 'Warehouse created successfully!');
        $this->redirect('/inventory/warehouses');
    }

    public function show($id): void {}
    public function edit($id): void {}
    public function update($id): void {}
    public function destroy($id): void {}
}
