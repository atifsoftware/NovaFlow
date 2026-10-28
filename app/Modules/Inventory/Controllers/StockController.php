<?php

namespace App\Modules\Inventory\Controllers;

use NovaFlow\Core\Controller;

class StockController extends Controller
{
    public function alerts(): void
    {
        $this->view('Inventory::items.index', ['title' => 'Stock Alerts & Reorder Levels'], 'main');
    }

    public function valuation(): void
    {
        $this->view('Inventory::items.index', ['title' => 'Inventory Valuation Report'], 'main');
    }
}
