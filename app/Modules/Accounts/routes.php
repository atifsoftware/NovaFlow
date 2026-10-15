<?php
// ============================================================
//  ERP Module — Accounts & Finance Routes
// ============================================================

$router->module('Accounts', function($router) {
    // Chart of Accounts
    $router->resource('chart-of-accounts', 'Accounts\ChartOfAccountsController');

    // Vouchers (Journal, Payment, Receipt)
    $router->resource('vouchers', 'Accounts\VoucherController');

    // Financial Statements & Ledgers
    $router->get('/ledger', 'Accounts\ReportController@ledger');
    $router->get('/trial-balance', 'Accounts\ReportController@trialBalance');
    $router->get('/profit-loss', 'Accounts\ReportController@profitLoss');
    $router->get('/balance-sheet', 'Accounts\ReportController@balanceSheet');
}, ['middleware' => ['auth']]);
