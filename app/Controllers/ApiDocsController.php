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
        
        $view = new View();
        return $view->render('api/explorer', [
            'apiData'    => $apiData,
            'pageTitle'  => 'NovaFlow API Explorer',
            'appName'    => APP_NAME,
            'appVersion' => APP_VERSION,
            'baseUrl'    => BASE_URL
        ], ''); // Pass empty layout to avoid wrapping in main layout
    }
}
