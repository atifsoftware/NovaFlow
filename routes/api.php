<?php
// ============================================================
//  NovaFlow MVC — API Routes (Version 1)
// ============================================================

$router->group(['prefix' => 'api/v1'], function($router) {
    
    // Public API Routes
    $router->post('/login', 'Api\V1\AuthApiController@login')->middleware('throttle:30,1');
    
    // Fallback for browser access to POST route
    $router->get('/login', 'Api\V1\AuthApiController@loginFallback');
    
    // Protected API Routes (Bearer JWT Token required)
    $router->group(['middleware' => 'api'], function($router) {
        $router->get('/profile', 'Api\V1\UserApiController@profile');
        $router->get('/users', 'Api\V1\UserApiController@index');
        $router->get('/users/{id}', 'Api\V1\UserApiController@show');
    });
});
