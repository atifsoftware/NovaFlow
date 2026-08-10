<?php
// ============================================================
//  NovaFlow MVC — Admin Routes (Protected)
// ============================================================

$router->group(['prefix' => 'admin', 'middleware' => 'auth'], function($router) {
    
    // Dashboard
    $router->get('/dashboard', 'AdminDashboardController@index');
    
    // Settings
    $router->get('/settings', 'AdminSettingsController@index');
    $router->post('/settings/update', 'AdminSettingsController@update');
    
    // User Management (CRUD)
    $router->get('/users', 'AdminUserController@index');
    $router->get('/users/create', 'AdminUserController@create');
    $router->post('/users/store', 'AdminUserController@store');
    $router->get('/users/edit/{id}', 'AdminUserController@edit');
    $router->post('/users/update/{id}', 'AdminUserController@update');
    $router->post('/users/delete/{id}', 'AdminUserController@delete');
    $router->post('/users/toggle-status/{id}', 'AdminUserController@toggleStatus');
});
