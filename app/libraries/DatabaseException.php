<?php

namespace NovaFlow\Core;

use RuntimeException;

/**
 * DatabaseException - Custom Exception for Database Operations
 */
class DatabaseException extends RuntimeException
{
    private $sql;
    private $params;
    private $context;

    public function __construct(
        $message,
        $code = 0,
        $previous = null,
        $sql = null,
        $params = null,
        array $context = []
    ) {
        parent::__construct($message, (int) $code, $previous);
        $this->sql = $sql;
        $this->params = $params;
        $this->context = $context;
    }

    public function getSql(): ?string
    {
        return $this->sql;
    }

    public function getParams(): ?array
    {
        return $this->params;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function __toString(): string
    {
        $output = __CLASS__ . ": [{$this->code}]: {$this->message}\n";

        if ($this->sql) {
            $output .= "SQL: {$this->sql}\n";
        }

        if ($this->params) {
            $output .= "Params: " . json_encode($this->params, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        }

        return $output;
    }
}
