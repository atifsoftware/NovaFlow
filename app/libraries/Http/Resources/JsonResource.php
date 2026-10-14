<?php

namespace NovaFlow\Core\Http\Resources;

use JsonSerializable;
use ArrayAccess;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;
use NovaFlow\Core\Pagination\LengthAwarePaginator;

/**
 * NovaFlow API JsonResource
 * Transforms models and objects into standardized, secure JSON API structures.
 */
class JsonResource implements JsonSerializable, ArrayAccess
{
    /**
     * The underlying resource instance
     */
    public mixed $resource;

    /**
     * Additional data to be added to top-level response
     */
    public array $additional = [];

    /**
     * The "data" wrapper key
     */
    public static ?string $wrap = 'data';

    public function __construct(mixed $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Static factory helper
     */
    public static function make(mixed $resource): static
    {
        return new static($resource);
    }

    /**
     * Create a new resource collection
     */
    public static function collection(mixed $resource): ResourceCollection
    {
        return new ResourceCollection($resource, static::class);
    }

    /**
     * Transform the resource into an array
     */
    public function toArray(Request $request): array
    {
        if (is_null($this->resource)) {
            return [];
        }

        if (is_array($this->resource)) {
            return $this->resource;
        }

        if (method_exists($this->resource, 'toArray')) {
            return $this->resource->toArray();
        }

        if (method_exists($this->resource, 'getAttributes')) {
            return $this->resource->getAttributes();
        }

        return (array) $this->resource;
    }

    /**
     * Get any additional data that should be returned with the resource array
     */
    public function with(Request $request): array
    {
        return [];
    }

    /**
     * Add additional meta data to the resource response
     */
    public function additional(array $data): static
    {
        $this->additional = array_merge($this->additional, $data);
        return $this;
    }

    /**
     * Resolve the resource to an array
     */
    public function resolve(?Request $request = null): array
    {
        $request = $request ?: new Request();

        $data = $this->toArray($request);

        if (static::$wrap !== null) {
            $data = [static::$wrap => $data];
        }

        $with = $this->with($request);
        if (!empty($with)) {
            $data = array_merge($data, $with);
        }

        if (!empty($this->additional)) {
            $data = array_merge($data, $this->additional);
        }

        return $data;
    }

    /**
     * Convert the resource to JSON
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Serialize to JSON data
     */
    public function jsonSerialize(): mixed
    {
        return $this->resolve();
    }

    /**
     * Dynamic property getter delegating to the underlying resource
     */
    public function __get(string $key): mixed
    {
        if (is_array($this->resource)) {
            return $this->resource[$key] ?? null;
        }

        if (is_object($this->resource)) {
            return $this->resource->{$key} ?? null;
        }

        return null;
    }

    /**
     * Dynamic method call delegating to the underlying resource
     */
    public function __call(string $method, array $parameters): mixed
    {
        if (is_object($this->resource) && method_exists($this->resource, $method)) {
            return $this->resource->{$method}(...$parameters);
        }

        return null;
    }

    // ArrayAccess implementation
    public function offsetExists(mixed $offset): bool
    {
        return is_array($this->resource) ? isset($this->resource[$offset]) : isset($this->resource->{$offset});
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_array($this->resource)) {
            $this->resource[$offset] = $value;
        } elseif (is_object($this->resource)) {
            $this->resource->{$offset} = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        if (is_array($this->resource)) {
            unset($this->resource[$offset]);
        } elseif (is_object($this->resource)) {
            unset($this->resource->{$offset});
        }
    }
}
