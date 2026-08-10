<?php
// ============================================================
//  NovaFlow MVC — API Routes (Version 1)
// ============================================================

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
    
    // User Management API Routes
    $router->get('/users', 'Api\V1\UserApiController@index');
    $router->get('/users/{id}', 'Api\V1\UserApiController@show');
    
    // Protected API Routes
    $router->group(['middleware' => 'api'], function($router) {
        $router->get('/profile', 'Api\V1\UserApiController@profile');
    });
});
