<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Inertia;
use NovaFlow\Core\Vite;
use NovaFlow\Core\Request;

class InertiaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Inertia::flushShared();
        Inertia::version('1.0.0');
        Inertia::setRootView('app');
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test-inertia';
        $_SERVER['HTTP_HOST'] = 'localhost';
        unset($_SERVER['HTTP_X_INERTIA']);
        unset($_SERVER['HTTP_X_INERTIA_VERSION']);
        unset($_SERVER['HTTP_X_INERTIA_PARTIAL_COMPONENT']);
        unset($_SERVER['HTTP_X_INERTIA_PARTIAL_DATA']);
        unset($_SERVER['HTTP_X_INERTIA_PARTIAL_EXCEPT']);

        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        if (file_exists($basePath . '/public/hot')) {
            @unlink($basePath . '/public/hot');
        }
    }

    protected function tearDown(): void
    {
        Inertia::flushShared();
        Vite::flush();
        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        if (file_exists($basePath . '/public/hot')) {
            @unlink($basePath . '/public/hot');
        }
        parent::tearDown();
    }

    public function testShareAndGetShared(): void
    {
        Inertia::share('appName', 'NovaFlow PHP');
        $this->assertEquals('NovaFlow PHP', Inertia::getShared('appName'));

        Inertia::share([
            'user' => ['name' => 'John'],
            'role' => 'admin'
        ]);

        $this->assertEquals(['name' => 'John'], Inertia::getShared('user'));
        $this->assertEquals('admin', Inertia::getShared('role'));

        Inertia::flushShared();
        $this->assertEmpty(Inertia::getShared());
    }

    public function testVersionAndManifestFallback(): void
    {
        Inertia::version('2.5.0');
        $this->assertEquals('2.5.0', Inertia::getVersion());

        Inertia::version(null);
        // Should fall back to manifest md5 if exists
        $version = Inertia::getVersion();
        $this->assertNotEmpty($version);
        $this->assertIsString($version);
    }

    public function testRootView(): void
    {
        Inertia::setRootView('custom_app');
        $this->assertEquals('custom_app', Inertia::getRootView());
    }

    public function testRenderInitialPageReturnsHtml(): void
    {
        Inertia::share('appName', 'NovaFlow PHP');

        ob_start();
        Inertia::render('Welcome', [
            'title' => 'Inertia Welcome'
        ]);
        $output = ob_get_clean();

        $this->assertStringContainsString('id="app"', $output);
        $this->assertStringContainsString('data-page=', $output);
        $this->assertStringContainsString('Inertia Welcome', $output);
        $this->assertStringContainsString('NovaFlow PHP', $output);
        $this->assertStringContainsString('<noscript>', $output);
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $output);
    }

    public function testRenderInitialPageWithArrayTitleSafely(): void
    {
        ob_start();
        Inertia::render('Welcome', [
            'title' => ['Admin', 'Dashboard']
        ]);
        $output = ob_get_clean();

        $this->assertStringContainsString('<title>Admin · Dashboard</title>', $output);
    }

    public function testRenderInertiaAjaxResponseReturnsJson(): void
    {
        $_SERVER['HTTP_X_INERTIA'] = 'true';
        Inertia::share('sharedKey', 'sharedValue');

        ob_start();
        Inertia::render('Dashboard', [
            'stats' => ['users' => 42]
        ]);
        $output = ob_get_clean();

        $data = json_decode($output, true);

        $this->assertIsArray($data);
        $this->assertEquals('Dashboard', $data['component']);
        $this->assertEquals('/test-inertia', $data['url']);
        $this->assertEquals('1.0.0', $data['version']);
        $this->assertEquals(42, $data['props']['stats']['users']);
        $this->assertEquals('sharedValue', $data['props']['sharedKey']);
    }

    public function testRenderPartialReloadOnly(): void
    {
        $_SERVER['HTTP_X_INERTIA'] = 'true';
        $_SERVER['HTTP_X_INERTIA_PARTIAL_COMPONENT'] = 'Users/Index';
        $_SERVER['HTTP_X_INERTIA_PARTIAL_DATA'] = 'users';

        $lazyEvaluated = false;

        ob_start();
        Inertia::render('Users/Index', [
            'users' => ['Alice', 'Bob'],
            'permissions' => function () use (&$lazyEvaluated) {
                $lazyEvaluated = true;
                return ['can_create' => true];
            }
        ]);
        $output = ob_get_clean();

        $data = json_decode($output, true);

        $this->assertIsArray($data);
        $this->assertEquals(['Alice', 'Bob'], $data['props']['users']);
        $this->assertArrayNotHasKey('permissions', $data['props']);
        $this->assertFalse($lazyEvaluated, 'Lazy props not in partial list should not be evaluated');
    }

    public function testRenderPartialReloadExcept(): void
    {
        $_SERVER['HTTP_X_INERTIA'] = 'true';
        $_SERVER['HTTP_X_INERTIA_PARTIAL_COMPONENT'] = 'Users/Index';
        $_SERVER['HTTP_X_INERTIA_PARTIAL_EXCEPT'] = 'sensitiveData';

        ob_start();
        Inertia::render('Users/Index', [
            'users' => ['Alice', 'Bob'],
            'sensitiveData' => 'secret-token'
        ]);
        $output = ob_get_clean();

        $data = json_decode($output, true);

        $this->assertIsArray($data);
        $this->assertEquals(['Alice', 'Bob'], $data['props']['users']);
        $this->assertArrayNotHasKey('sensitiveData', $data['props']);
    }

    public function testRenderPartialReloadNestedDotNotation(): void
    {
        $_SERVER['HTTP_X_INERTIA'] = 'true';
        $_SERVER['HTTP_X_INERTIA_PARTIAL_COMPONENT'] = 'Profile';
        $_SERVER['HTTP_X_INERTIA_PARTIAL_DATA'] = 'user.email';

        ob_start();
        Inertia::render('Profile', [
            'user' => [
                'name' => 'John',
                'email' => 'john@example.com'
            ]
        ]);
        $output = ob_get_clean();

        $data = json_decode($output, true);

        $this->assertIsArray($data);
        $this->assertEquals('john@example.com', $data['props']['user']['email']);
    }

    public function testResolveAbsoluteUrlWithSubfolder(): void
    {
        // When BASE_URL has a subfolder path like /NovaFlow
        $url1 = Inertia::resolveAbsoluteUrl('/login');
        $this->assertStringContainsString('/login', $url1);
        if (defined('BASE_URL') && str_contains(BASE_URL, '/NovaFlow')) {
            $this->assertEquals('http://localhost/NovaFlow/login', $url1);
        }

        // When URL already has /NovaFlow, ensure it is not duplicated
        $url2 = Inertia::resolveAbsoluteUrl('/NovaFlow/dashboard');
        $this->assertStringNotContainsString('/NovaFlow/NovaFlow', $url2);
    }

    public function testVersionMismatchReturns409WithAbsoluteUrl(): void
    {
        $_SERVER['HTTP_X_INERTIA'] = 'true';
        $_SERVER['HTTP_X_INERTIA_VERSION'] = 'old-version-0.9';
        Inertia::version('1.0.0');

        ob_start();
        Inertia::render('Home', []);
        ob_get_clean();

        $this->assertEquals(409, http_response_code());
    }

    public function testInertiaLocationSets409WhenInertiaRequest(): void
    {
        $_SERVER['HTTP_X_INERTIA'] = 'true';

        ob_start();
        Inertia::location('/new-url');
        ob_get_clean();

        $this->assertEquals(409, http_response_code());
    }

    public function testInertiaHelperWithNullComponent(): void
    {
        inertia(null, ['globalSetting' => 'enabled']);
        $this->assertEquals('enabled', Inertia::getShared('globalSetting'));

        inertia();
        $this->assertTrue(true);
    }

    public function testViteSelfHealsStaleHotFile(): void
    {
        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        $hotFile = $basePath . '/public/hot';

        // Write a dead port that cannot be connected to
        file_put_contents($hotFile, "http://localhost:59999\n");

        Vite::flush();
        $tags = Vite::tags('resources/js/app.jsx');

        // Hot file should have been detected as dead and self-healed (deleted)
        $this->assertFileDoesNotExist($hotFile);

        // Tags should fall back to production bundle
        $this->assertStringContainsString('<script type="module"', $tags);
        $this->assertStringNotContainsString('59999', $tags);
    }

    public function testViteTagsFallbackEmitsBothCssAndJs(): void
    {
        Vite::flush();
        $tags = Vite::tags('resources/js/app.jsx');

        $this->assertStringContainsString('<link rel="stylesheet"', $tags);
        $this->assertStringContainsString('<script type="module"', $tags);
    }

    public function testViteProbeDoesNotLeakWarningsIntoPage(): void
    {
        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        $hotFile = $basePath . '/public/hot';
        file_put_contents($hotFile, "http://localhost:59999\n");

        // Simulate a global handler that reports every diagnostic instead of
        // honouring error_reporting() (the previous behaviour of the debug handler).
        $captured = '';
        set_error_handler(static function ($errno, $errstr) use (&$captured): bool {
            $captured .= $errstr;
            return true;
        }, E_WARNING | E_NOTICE);

        ob_start();
        try {
            Vite::flush();
            $tags = Vite::tags('resources/js/app.jsx');
        } finally {
            $output = ob_get_clean();
            restore_error_handler();
        }

        $this->assertSame('', trim($output), 'Dead hot file must not print diagnostics into the page.');
        $this->assertSame('', $captured, 'The socket probe must not surface warnings.');
        $this->assertStringNotContainsString('fsockopen', $tags);
        $this->assertFileDoesNotExist($hotFile);
    }

    public function testErrorHandlerRespectsSuppressedWarnings(): void
    {
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__, 2));
        }

        $previous = error_reporting(0);
        ob_start();
        try {
            $result = \NovaFlow\Core\IntelligentErrorHandler::handleError(
                E_WARNING,
                'fsockopen(): suppressed diagnostic',
                __FILE__,
                __LINE__
            );
        } finally {
            $output = ob_get_clean();
            error_reporting($previous);
        }

        $this->assertTrue($result);
        $this->assertSame('', $output, '@-suppressed warnings must not render the debug view.');
    }
}
