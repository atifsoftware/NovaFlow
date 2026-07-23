<?php

namespace NovaFlow\Core;

/**
 * Response Class
 */
class Response
{
    /**
     * Set HTTP status code
     */
    public function setStatusCode(int $code): void
    {
        http_response_code($code);
    }

    /**
     * Redirect to another URL
     */
    public function redirect(string $url): void
    {
        header('Location: ' . BASE_URL . $url);
        exit;
    }

    /**
     * Return JSON response
     */
    public function json(array $data, int $code = 200): void
    {
        if (ob_get_length()) ob_clean();
        $this->setStatusCode($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
