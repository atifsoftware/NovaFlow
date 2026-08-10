<?php
// ============================================================
//  NovaFlow MVC — Web Routes (Frontend & Auth)
// ============================================================

// Home Route
$router->get('/', 'HomeController@index');

// Authentication Routes
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@postLogin');

$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@postRegister');

$router->get('/logout', 'AuthController@logout');

// Documentation Routes
$router->get('/docs', 'DocsController@index');
