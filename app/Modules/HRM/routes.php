<?php
// ============================================================
//  ERP Module — HRM Routes
// ============================================================

$router->module('HRM', function($router) {
    $router->get('/', 'HRM\HRMController@index');
    $router->resource('hrms', 'HRM\HRMController');
});
