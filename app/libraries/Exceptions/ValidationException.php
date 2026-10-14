<?php

namespace NovaFlow\Core\Exceptions;

use Exception;

/**
 * ValidationException
 * Thrown when form or API request data fails validation rules.
 */
class ValidationException extends Exception
{
    protected array $errors = [];

    public function __construct(array $errors, string $message = 'The given data was invalid.')
    {
        parent::__construct($message, 422);
        $this->errors = $errors;
    }

    /**
     * Get the array of validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
