<?php

namespace NovaFlow\Core;

use ReflectionClass;
use ReflectionMethod;

/**
 * ApiExplorer
 * Automatically generates API documentation by scanning controllers and DocBlocks.
 */
class ApiExplorer
{
    protected array $apiDocs = [];

    /**
     * Scan the registered routes and extract API documentation.
     */
    public function scan(Router $router): array
    {
        $allRoutes = $router->getRoutes();
        $docs = [];

        foreach ($allRoutes as $method => $paths) {
            foreach ($paths as $path => $info) {
                // Only document routes starting with /api
                if (!str_starts_with($path, '/api')) {
                    continue;
                }

                $callback = $info['callback'];
                if (is_string($callback) && str_contains($callback, '@')) {
                    [$controller, $action] = explode('@', $callback);
                    
                    // Resolve full controller name
                    if (!str_starts_with($controller, 'App\\Controllers\\')) {
                        $controller = 'App\\Controllers\\' . ltrim($controller, '\\');
                    }

                    if (class_exists($controller)) {
                        $docs[] = $this->parseMethod($method, $path, $controller, $action, $info['middleware']);
                    }
                }
            }
        }

        return $docs;
    }

    /**
     * Parse a specific controller method for DocBlock metadata.
     */
    protected function parseMethod($httpMethod, $path, $controller, $action, $middlewares): array
    {
        $reflection = new ReflectionMethod($controller, $action);
        $docComment = $reflection->getDocComment() ?: '';

        $doc = [
            'method'      => strtoupper($httpMethod),
            'path'        => $path,
            'controller'  => basename(str_replace('\\', '/', $controller)),
            'action'      => $action,
            'summary'     => $this->getTagValue($docComment, 'Summary') ?: 'No description provided.',
            'parameters'  => $this->parseParams($docComment),
            'response'    => $this->getTagValue($docComment, 'Response'),
            'middleware'  => $middlewares,
            'id'          => md5($httpMethod . $path)
        ];

        return $doc;
    }

    /**
     * Simple regex parser for custom DocBlock tags
     */
    protected function getTagValue(string $doc, string $tag): ?string
    {
        preg_match('/@' . $tag . '\s+(.*)/i', $doc, $matches);
        return isset($matches[1]) ? trim($matches[1]) : null;
    }

    /**
     * Parse @Param tags: @Param name (type, description)
     */
    protected function parseParams(string $doc): array
    {
        preg_match_all('/@Param\s+([a-zA-Z0-9_]+)\s*\(([^)]+)\)/i', $doc, $matches);
        
        $params = [];
        if (!empty($matches[0])) {
            foreach ($matches[1] as $index => $name) {
                $details = explode(',', $matches[2][$index]);
                $params[] = [
                    'name'        => trim($name),
                    'type'        => trim($details[0] ?? 'string'),
                    'description' => trim($details[1] ?? ''),
                    'required'    => str_contains($matches[2][$index], 'required')
                ];
            }
        }
        return $params;
    }
}
