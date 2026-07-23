<?php

namespace App\Controllers;

use NovaFlow\Core\Controller;
use NovaFlow\Core\ApiExplorer;
use NovaFlow\Core\Router;
use NovaFlow\Core\View;

/**
 * ApiDocsController
 * Renders the Interactive API Explorer
 */
class ApiDocsController extends Controller
{
    protected $explorer;

    public function __construct()
    {
        $this->explorer = new ApiExplorer();
        parent::__construct();
    }

    /**
     * Show API Explorer
     */
    public function index(Router $router)
    {
        $apiData = $this->explorer->scan($router);

        // Generate Swagger/OpenAPI JSON
        $swagger = $this->generateSwagger($apiData);

        $view = new View();
        return $view->render('api/explorer', [
            'apiData'    => $apiData,
            'swagger'    => $swagger,
            'pageTitle'  => 'NovaFlow API Explorer',
            'appName'    => APP_NAME,
            'appVersion' => APP_VERSION,
            'baseUrl'    => BASE_URL
        ], ''); // Pass empty layout to avoid wrapping in main layout
    }

    private function generateSwagger(array $apiData): string
    {
        $swagger = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => APP_NAME,
                'version' => APP_VERSION,
                'description' => 'API Documentation'
            ],
            'paths' => []
        ];

        foreach ($apiData as $endpoint) {
            $swagger['paths'][$endpoint['path']][$endpoint['method']] = [
                'summary' => $endpoint['summary'],
                'responses' => [
                    '200' => ['description' => 'Success']
                ]
            ];
        }

        return json_encode($swagger, JSON_PRETTY_PRINT);
    }
}
