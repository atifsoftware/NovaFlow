<?php

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\View;

class ModularViewTest extends TestCase
{
    private string $tempViewsDir;
    private string $tempModulesDir;
    private View $viewService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempViewsDir = sys_get_temp_dir() . '/nf_views_' . uniqid();
        $this->tempModulesDir = sys_get_temp_dir() . '/nf_modules_' . uniqid();

        mkdir($this->tempViewsDir . '/layouts', 0777, true);
        mkdir($this->tempModulesDir . '/Sales/Views/invoices', 0777, true);
        mkdir($this->tempModulesDir . '/Accounts/Views/ledger', 0777, true);

        // Global Layout
        file_put_contents($this->tempViewsDir . '/layouts/main.php', '<html><body><main><?= $content ?></main></body></html>');
        
        // Global View
        file_put_contents($this->tempViewsDir . '/welcome.php', '<h1>Welcome to <?= $name ?></h1>');

        // Module View (Sales)
        file_put_contents($this->tempModulesDir . '/Sales/Views/invoices/index.php', '<div>Sales Invoices: <?= $count ?> items</div>');

        // Module View (Accounts)
        file_put_contents($this->tempModulesDir . '/Accounts/Views/ledger/report.php', '<div>Accounts Ledger: $<?= $balance ?></div>');

        $this->viewService = new View($this->tempViewsDir, $this->tempModulesDir);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempViewsDir);
        $this->removeDirectory($this->tempModulesDir);
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

    public function testGlobalViewRendering(): void
    {
        $html = $this->viewService->render('welcome', ['name' => 'NovaFlow'], 'main');
        $this->assertStringContainsString('<html><body><main><h1>Welcome to NovaFlow</h1></main></body></html>', $html);
    }

    public function testModularNamespacedViewRendering(): void
    {
        $html = $this->viewService->render('Sales::invoices.index', ['count' => 42], 'main');
        $this->assertStringContainsString('<main><div>Sales Invoices: 42 items</div></main>', $html);
    }

    public function testModularCaseInsensitiveNamespace(): void
    {
        $html = $this->viewService->render('accounts::ledger.report', ['balance' => 9500], 'none');
        $this->assertStringContainsString('<div>Accounts Ledger: $9500</div>', $html);
    }

    public function testModularDotNotationFallback(): void
    {
        $html = $this->viewService->render('Sales.invoices.index', ['count' => 15], 'none');
        $this->assertStringContainsString('<div>Sales Invoices: 15 items</div>', $html);
    }

    public function testViewExistsHelper(): void
    {
        $this->assertTrue($this->viewService->exists('welcome'));
        $this->assertTrue($this->viewService->exists('Sales::invoices.index'));
        $this->assertTrue($this->viewService->exists('accounts::ledger.report'));
        $this->assertFalse($this->viewService->exists('non_existent_view'));
        $this->assertFalse($this->viewService->exists('Inventory::missing.view'));
    }

    public function testMissingViewThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('View file not found');
        $this->viewService->render('unknown_view_file');
    }
}
