<?php

namespace NovaFlow\Core;

/**
 * ApiController
 * Base Controller for API Routes
 * Provides structured JSON responses
 */
abstract class ApiController extends Controller
{
    public function __construct()
    {
        // No CSRF for APIs (Token-based)
        $this->skipCsrf = true;
        parent::__construct();
        
        // Ensure JSON response header
        header('Content-Type: application/json; charset=utf-8');
    }

    /**
     * Standard Success Response
     */
    protected function success(mixed $data = null, string $message = 'Success', int $code = 200): void
    {
        $response = [
            'status'  => 'success',
            'message' => $message,
            'data'    => $data
        ];
        
        $this->json($response, $code);
    }

    /**
     * Standard Error Response
     */
    protected function error(string $message = 'An error occurred', int $code = 400): void
    {
        $response = [
            'status'  => 'error',
            'message' => $message
        ];
        
        $this->json($response, $code);
    }

    /**
     * Unauthorized Error Response
     */
    protected function unauthorized(string $message = 'Unauthorized'): void
    {
        $this->error($message, 401);
    }

    /**
     * Forbidden Error Response
     */
    protected function forbidden(string $message = 'Forbidden'): void
    {
        $this->error($message, 403);
    }

    /**
     * Validation Error Response
     */
    protected function validationError(array $errors): void
    {
        $response = [
            'status'  => 'error',
            'message' => 'Validation Failed',
            'errors'  => $errors
        ];
        
        $this->json($response, 422);
    }
}
