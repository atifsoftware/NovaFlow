<?php

namespace NovaFlow\Core;

/**
 * View Service - Handles template rendering and layout management
 */
class View
{
    protected string $viewPath;
    protected string $layoutPath;
    protected array $sharedData = [];

    public function __construct()
    {
        $this->viewPath = VIEW_PATH;
        $this->layoutPath = VIEW_PATH . '/layouts';
    }

    /**
     * Share data across all views
     */
    public function share(string $key, mixed $value): void
    {
        $this->sharedData[$key] = $value;
    }

    /**
     * Render a view file
     */
    public function render(string $view, array $data = [], string $layout = 'main'): string
    {
        // Extract shared data and local data
        $data = array_merge($this->sharedData, $data);
        extract($data);

        // Resolve view file path
        $viewFile = $this->viewPath . '/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: $viewFile");
        }

        // Buffer view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Resolve layout path
        $layoutFile = $this->layoutPath . '/' . $layout . '.php';

        if (file_exists($layoutFile)) {
            ob_start();
            require $layoutFile;
            return ob_get_clean();
        }

        return $content;
    }

    /**
     * Render a component (partial view)
     */
    public function component(string $name, array $data = []): string
    {
        extract($data);
        $file = $this->viewPath . '/components/' . str_replace('.', '/', $name) . '.php';
        
        if (!file_exists($file)) {
            return "<!-- Component $name not found -->";
        }

        ob_start();
        require $file;
        return ob_get_clean();
    }
}
