<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Core\TemplateEngine;

class TemplateEngineTest extends TestCase
{
    private string $viewsDir;
    private string $cacheDir;
    private TemplateEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viewsDir = sys_get_temp_dir() . '/novaflow_test_views_' . uniqid();
        $this->cacheDir = sys_get_temp_dir() . '/novaflow_test_cache_' . uniqid();

        mkdir($this->viewsDir, 0777, true);
        mkdir($this->cacheDir, 0777, true);

        $this->engine = new TemplateEngine($this->viewsDir, $this->cacheDir);
    }

    protected function tearDown(): void
    {
        // Cleanup temp dirs
        $this->removeDirectory($this->viewsDir);
        $this->removeDirectory($this->cacheDir);
        parent::tearDown();
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testExpressionEscaping(): void
    {
        file_put_contents($this->viewsDir . '/test.php', '<h1>Hello {{ $user->name }}</h1><p>{{ $profile["email"] }}</p>');

        $userObj = (object)['name' => '<script>alert(1)</script>'];
        $userArr = ['email' => 'test@example.com'];

        $html = $this->engine->render('test', ['user' => $userObj, 'profile' => $userArr]);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringContainsString('test@example.com', $html);
    }

    public function testRawOutput(): void
    {
        file_put_contents($this->viewsDir . '/raw.php', '<div>{!! $html !!}</div>');
        $html = $this->engine->render('raw', ['html' => '<b>Bold Text</b>']);
        $this->assertStringContainsString('<b>Bold Text</b>', $html);
    }

    public function testInheritanceAndSectionsWithCache(): void
    {
        // 1. Create layout
        file_put_contents($this->viewsDir . '/layout.php', '<main>@yield("content")</main>');

        // 2. Create child template
        file_put_contents($this->viewsDir . '/child.php', '@extends("layout")@section("content")<h1>Section Header: {{ $title }}</h1>@endsection');

        // First render (creates cache)
        $html1 = $this->engine->render('child', ['title' => 'First Run']);
        $this->assertStringContainsString('<main><h1>Section Header: First Run</h1></main>', $html1);

        // Second render from cache (should work identically with new data!)
        $html2 = $this->engine->render('child', ['title' => 'Second Run']);
        $this->assertStringContainsString('<main><h1>Section Header: Second Run</h1></main>', $html2);
    }
}
