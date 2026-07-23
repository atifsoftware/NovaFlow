<?php

namespace NovaFlow\Core;

/**
 * Middleware Interface
 * All middleware classes must implement this to handle requests.
 */
interface Middleware
{
    /**
     * Handle the incoming request.
     * 
     * @param Request $request
     * @param Response $response
     * @param array $args Optional arguments passed to the middleware
     * @return bool|void Return true to proceed, false or redirect/exit to stop.
     */
    public function handle(Request $request, Response $response, array $args = []): bool;
}
