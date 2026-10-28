<?php
// ============================================================
//  NovaFlow MVC — Web Routes (Frontend & Auth)
// ============================================================

// Home Route
$router->get('/', 'HomeController@index');

// Authentication Routes
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@postLogin')->middleware(['csrf', 'throttle:10,1']);
$router->get('/auth/login', 'AuthController@login');
$router->post('/auth/login', 'AuthController@postLogin')->middleware(['csrf', 'throttle:10,1']);

$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@postRegister')->middleware(['csrf', 'throttle:10,1']);

$router->get('/logout', 'AuthController@logout');

// Documentation Routes
$router->get('/docs', 'DocsController@index');
$router->get('/api/docs', 'ApiDocsController@index');
