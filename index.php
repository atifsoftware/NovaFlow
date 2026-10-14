<?php
/**
 * NovaFlow MVC — Entry Point Redirector
 * Ensures seamless access when navigating directly to the project root in Apache / Nginx / XAMPP.
 */

// If requested through web server, forward to public directory
require_once __DIR__ . '/public/index.php';
