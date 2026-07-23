<?php

namespace NovaFlow\Core;

/**
 * RawExpression Class
 * Represents a raw SQL expression to avoid escaping
 */
class RawExpression
{
    private $expression;
    private $bindings;

    /**
     * Constructor
     * 
     * @param string $expression Raw SQL string
     * @param array $bindings Parameters for the expression
     */
    public function __construct(string $expression, array $bindings = [])
    {
        $this->expression = $expression;
        $this->bindings = $bindings;
    }

    /**
     * Get the raw SQL expression
     * 
     * @return string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * Get bindings
     * 
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Convert to string
     * 
     * @return string
     */
    public function __toString(): string
    {
        return $this->expression;
    }
}
