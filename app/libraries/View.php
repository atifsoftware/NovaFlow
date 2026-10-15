<?php

namespace NovaFlow\Core;

/**
 * View Service - Handles template rendering and layout management
 * Supports both Global views (app/views/) and Modular ERP views (app/Modules/<Name>/Views/)
 */
class View
{
    protected string $viewPath;
    protected string $layoutPath;
    protected string $modulesPath;
    protected array $sharedData = [];

    public function __construct(?string $viewPath = null, ?string $modulesPath = null)
    {
        $this->viewPath = $viewPath ?: (defined('VIEW_PATH') ? VIEW_PATH : dirname(__DIR__) . '/views');
        $this->layoutPath = $this->viewPath . '/layouts';
        $this->modulesPath = $modulesPath ?: (defined('MODULES_PATH') ? MODULES_PATH : (defined('APP_PATH') ? APP_PATH . '/Modules' : dirname(__DIR__) . '/Modules'));
    }

    /**
     * Share data across all views
     */
    public function share(string $key, mixed $value): void
    {
        $this->sharedData[$key] = $value;
    }

    /**
     * Check if a view exists (either global or modular)
     */
    public function exists(string $view): bool
    {
        try {
            $this->resolveViewFile($view);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Resolve a view path supporting both app/views and app/Modules/<Name>/Views
     *
     * Supported syntaxes:
     * 1. Modular Namespaced: "Sales::invoices.index" or "accounts::ledger" -> app/Modules/Sales/Views/invoices/index.php
     * 2. Global View: "welcome" or "auth.login" -> app/views/welcome.php or app/views/auth/login.php
     * 3. Dot Module Fallback: "Sales.invoices.index" -> app/Modules/Sales/Views/invoices/index.php
     */
    public function resolveViewFile(string $view): string
    {
        // 1. Explicit Module syntax: "Sales::invoices.index"
        if (str_contains($view, '::')) {
            [$module, $viewPath] = explode('::', $view, 2);
            $moduleName = trim($module);
            $relativeFile = str_replace('.', '/', trim($viewPath)) . '.php';

            // Direct check
            $file = $this->modulesPath . '/' . ucfirst($moduleName) . '/Views/' . $relativeFile;
            if (file_exists($file)) {
                return $file;
            }

            // Case-insensitive module directory lookup
            if (is_dir($this->modulesPath)) {
                $dirs = scandir($this->modulesPath);
                if ($dirs !== false) {
                    foreach ($dirs as $dir) {
                        if ($dir === '.' || $dir === '..') continue;
                        if (strcasecmp($dir, $moduleName) === 0) {
                            $caseFile = $this->modulesPath . '/' . $dir . '/Views/' . $relativeFile;
                            if (file_exists($caseFile)) {
                                return $caseFile;
                            }
                        }
                    }
                }
            }

            throw new \Exception("Module view not found: [$view] (Looked in $file)");
        }

        // 2. Global View: app/views/<view>.php
        $globalFile = $this->viewPath . '/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($globalFile)) {
            return $globalFile;
        }

        // 3. Fallback: check if first segment matches an ERP Module directory
        $parts = explode('.', $view);
        if (count($parts) > 1 && is_dir($this->modulesPath)) {
            $candidateModule = $parts[0];
            $candidateView = implode('/', array_slice($parts, 1)) . '.php';

            $dirs = scandir($this->modulesPath);
            if ($dirs !== false) {
                foreach ($dirs as $dir) {
                    if ($dir === '.' || $dir === '..') continue;
                    if (strcasecmp($dir, $candidateModule) === 0) {
                        $moduleFile = $this->modulesPath . '/' . $dir . '/Views/' . $candidateView;
                        if (file_exists($moduleFile)) {
                            return $moduleFile;
                        }
                    }
                }
            }
        }

        throw new \Exception("View file not found: [$view] (Checked global: $globalFile)");
    }

    /**
     * Resolve layout file path (supports both global layouts and module layouts)
     */
    public function resolveLayoutFile(string $layout): ?string
    {
        if (empty($layout) || $layout === 'none') {
            return null;
        }

        // 1. Module layout: "Sales::layouts.pos"
        if (str_contains($layout, '::')) {
            [$module, $layoutPath] = explode('::', $layout, 2);
            $moduleName = trim($module);
            $relativeFile = str_replace('.', '/', trim($layoutPath)) . '.php';

            $file = $this->modulesPath . '/' . ucfirst($moduleName) . '/Views/' . $relativeFile;
            if (file_exists($file)) {
                return $file;
            }
        }

        // 2. Global Layout: app/views/layouts/<layout>.php
        $globalLayout = $this->layoutPath . '/' . str_replace('.', '/', $layout) . '.php';
        if (file_exists($globalLayout)) {
            return $globalLayout;
        }

        return null;
    }

    /**
     * Render a view file (supports both global and modular views)
     */
    public function render(string $view, array $data = [], string $layout = 'main'): string
    {
        // Extract shared data and local data
        $data = array_merge($this->sharedData, $data);
        extract($data);

        // Resolve view file path
        $viewFile = $this->resolveViewFile($view);

        // Buffer view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Resolve layout
        $layoutFile = $this->resolveLayoutFile($layout);
        if ($layoutFile && file_exists($layoutFile)) {
            ob_start();
            require $layoutFile;
            return ob_get_clean();
        }

        return $content;
    }

    /**
     * Render a component (partial view)
     * Supports "Sales::components.invoice_row" or global "components.alert"
     */
    public function component(string $name, array $data = []): string
    {
        extract($data);

        // Module component: "Sales::components.badge"
        if (str_contains($name, '::')) {
            [$module, $componentPath] = explode('::', $name, 2);
            $file = $this->modulesPath . '/' . ucfirst(trim($module)) . '/Views/components/' . str_replace('.', '/', trim($componentPath)) . '.php';
            if (file_exists($file)) {
                ob_start();
                require $file;
                return ob_get_clean();
            }
        }

        // Global Component: app/views/components/<name>.php
        $file = $this->viewPath . '/components/' . str_replace('.', '/', $name) . '.php';
        if (file_exists($file)) {
            ob_start();
            require $file;
            return ob_get_clean();
        }

        return "<!-- Component $name not found -->";
    }

    /**
     * Get configured global views path
     */
    public function getViewPath(): string
    {
        return $this->viewPath;
    }

    /**
     * Get configured ERP modules path
     */
    public function getModulesPath(): string
    {
        return $this->modulesPath;
    }
}
