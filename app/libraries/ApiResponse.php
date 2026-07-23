<?php

namespace NovaFlow\Core;

/**
 * API Response Wrapper
 * Standardized JSON response for APIs
 */
class ApiResponse
{
    /**
     * Success response
     */
    public static function success($data = null, string $message = 'Success', int $code = 200): void
    {
        self::json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Error response
     */
    public static function error(string $message = 'Error', $errors = null, int $code = 400): void
    {
        $response = [
            'status' => 'error',
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        self::json($response, $code);
    }

    /**
     * Created response
     */
    public static function created($data = null, string $message = 'Created successfully'): void
    {
        self::success($data, $message, 201);
    }

    /**
     * Updated response
     */
    public static function updated($data = null, string $message = 'Updated successfully'): void
    {
        self::success($data, $message, 200);
    }

    /**
     * Deleted response
     */
    public static function deleted(string $message = 'Deleted successfully'): void
    {
        self::success(null, $message, 200);
    }

    /**
     * Not found response
     */
    public static function notFound(string $message = 'Resource not found'): void
    {
        self::error($message, null, 404);
    }

    /**
     * Unauthorized response
     */
    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, null, 401);
    }

    /**
     * Forbidden response
     */
    public static function forbidden(string $message = 'Forbidden'): void
    {
        self::error($message, null, 403);
    }

    /**
     * Validation error response
     */
    public static function validationError(array $errors): void
    {
        self::error('Validation failed', $errors, 422);
    }

    /**
     * Send JSON response
     */
    public static function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send raw JSON (for custom responses)
     */
    public static function raw(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}