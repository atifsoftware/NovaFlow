<?php
// ============================================================
//  ERP Module — Inventory Routes
// ============================================================

$router->module('Inventory', function($router) {
    // Items / Products CRUD
    $router->resource('items', 'Inventory\ItemController');

    // Stock Adjustments
    $router->resource('adjustments', 'Inventory\AdjustmentController');

    // Warehouses
    $router->resource('warehouses', 'Inventory\WarehouseController');

    // Stock Reports
    $router->get('/stock-alert', 'Inventory\StockController@alerts');
    $router->get('/valuation', 'Inventory\StockController@valuation');
}, ['middleware' => ['auth']]);
