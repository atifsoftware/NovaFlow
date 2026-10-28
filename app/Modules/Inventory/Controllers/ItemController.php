<?php

namespace App\Modules\Inventory\Controllers;

use NovaFlow\Core\Controller;

class ItemController extends Controller
{
    public function index(): void
    {
        $this->view('Inventory::items.index', [
            'title' => 'Inventory Items — Products'
        ], 'main');
    }

    public function create(): void
    {
        $this->view('Inventory::items.create', ['title' => 'Add Inventory Item'], 'main');
    }

    public function store(): void
    {
        flash('success', 'Item created successfully!');
        $this->redirect('/inventory/items');
    }

    public function show($id): void
    {
        $this->view('Inventory::items.show', ['id' => $id, 'title' => 'Item Details'], 'main');
    }

    public function edit($id): void
    {
        $this->view('Inventory::items.edit', ['id' => $id, 'title' => 'Edit Item'], 'main');
    }

    public function update($id): void
    {
        flash('success', 'Item updated successfully!');
        $this->redirect('/inventory/items');
    }

    public function destroy($id): void
    {
        flash('success', 'Item deleted successfully!');
        $this->redirect('/inventory/items');
    }
}
