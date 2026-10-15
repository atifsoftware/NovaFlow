<?php
// ============================================================
//  ERP Module — Sales Routes
// ============================================================

$router->module('Sales', function($router) {
    // Invoices Resource: index, create, store, show, edit, update, destroy
    $router->resource('invoices', 'Sales\InvoiceController');

    // Quotations Resource
    $router->resource('quotations', 'Sales\QuotationController');

    // Customers Resource
    $router->resource('customers', 'Sales\CustomerController');

    // Sales Reports
    $router->get('/reports/summary', 'Sales\ReportController@summary');
    $router->get('/reports/by-customer', 'Sales\ReportController@byCustomer');
}, ['middleware' => ['auth']]);
