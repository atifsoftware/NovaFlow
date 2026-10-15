<?php
// ============================================================
//  NovaFlow MVC — Master Route Loader (Backward Compatibility)
// ============================================================

if (isset($router) && method_exists($router, 'loadAllRoutes')) {
    $router->loadAllRoutes();
}
