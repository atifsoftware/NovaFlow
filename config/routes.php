<?php
// ============================================================
//  NovaFlow MVC — Route Configuration
// ============================================================

// 1. Frontend Routes
$router->get('/', 'HomeController@index');
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@postLogin');
$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@postRegister');
$router->get('/logout', 'AuthController@logout');
$router->get('/docs', 'DocsController@index');
$router->get('/api/docs', 'ApiDocsController@index');

// 2. Admin Routes (Protected)
$router->group(['prefix' => 'admin', 'middleware' => 'auth'], function($router) {
    $router->get('/dashboard', 'AdminDashboardController@index');
    
    // Settings
    $router->get('/settings', 'AdminSettingsController@index');
    $router->post('/settings/update', 'AdminSettingsController@update');
    
    // User Management (Users CRUB)
    $router->get('/users', 'AdminUserController@index');
    $router->get('/users/create', 'AdminUserController@create');
    $router->post('/users/store', 'AdminUserController@store');
    $router->get('/users/edit/{id}', 'AdminUserController@edit');
    $router->post('/users/update/{id}', 'AdminUserController@update');
    $router->post('/users/delete/{id}', 'AdminUserController@delete');
    $router->post('/users/toggle-status/{id}', 'AdminUserController@toggleStatus');
});

// 3. API V1 Routes
$router->group(['prefix' => 'api/v1'], function($router) {
    // Public API Routes
    $router->post('/login', 'Api\V1\AuthApiController@login');
    
    // Fallback for browser access to POST route
    $router->get('/login', function($req, $res) {
        return json_encode([
            'status' => 'error', 
            'message' => 'Method Not Allowed. Please use POST for Login.',
            'docs' => 'Visit /api/docs for more info.'
        ]);
    });
    
    // Protected API Routes
    $router->group(['middleware' => 'api'], function($router) {
        $router->get('/profile', 'Api\V1\UserApiController@profile');
    });
});
