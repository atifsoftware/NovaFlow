<?php

namespace NovaFlow\Core\Http\Resources;

use Countable;
use IteratorAggregate;
use ArrayIterator;
use Traversable;
use NovaFlow\Core\Request;
use NovaFlow\Core\Pagination\LengthAwarePaginator;

/**
 * NovaFlow API ResourceCollection
 * Represents a collection of resources, with optional pagination metadata.
 */
class ResourceCollection extends JsonResource implements Countable, IteratorAggregate
{
    /**
     * The resource that this collection collects
     */
    public string $collects = JsonResource::class;

    public function __construct(mixed $resource, ?string $collects = null)
    {
        parent::__construct($resource);
        if ($collects) {
            $this->collects = $collects;
        }
    }

    /**
     * Transform the resource collection into an array
     */
    public function toArray(Request $request): array
    {
        $items = $this->extractItems();
        $collectsClass = $this->collects;

        $results = [];
        foreach ($items as $item) {
            if ($item instanceof JsonResource) {
                $results[] = $item->toArray($request);
            } else {
                $results[] = (new $collectsClass($item))->toArray($request);
            }
        }

        return $results;
    }

    /**
     * Resolve the resource collection including pagination metadata if present
     */
    public function resolve(?Request $request = null): array
    {
        $request = $request ?: new Request();

        $data = $this->toArray($request);

        $response = [];

        if (static::$wrap !== null) {
            $response[static::$wrap] = $data;
        } else {
            $response = $data;
        }

        // Check if underlying resource is a paginator
        if ($this->resource instanceof LengthAwarePaginator) {
            $paginatorArray = $this->resource->toArray();
            $response['links'] = [
                'first' => $paginatorArray['first_page_url'] ?? null,
                'last'  => $paginatorArray['last_page_url'] ?? null,
                'prev'  => $paginatorArray['prev_page_url'] ?? null,
                'next'  => $paginatorArray['next_page_url'] ?? null,
            ];
            $response['meta'] = [
                'current_page' => $this->resource->currentPage(),
                'from'         => $this->resource->firstItem(),
                'last_page'    => $this->resource->lastPage(),
                'per_page'     => $this->resource->perPage(),
                'to'           => $this->resource->lastItem(),
                'total'        => $this->resource->total(),
            ];
        }

        $with = $this->with($request);
        if (!empty($with)) {
            $response = array_merge($response, $with);
        }

        if (!empty($this->additional)) {
            $response = array_merge($response, $this->additional);
        }

        return $response;
    }

    /**
     * Extract raw items from resource (handles array, Paginator, etc.)
     */
    protected function extractItems(): iterable
    {
        if ($this->resource instanceof LengthAwarePaginator) {
            return $this->resource->items();
        }

        if (is_array($this->resource)) {
            return $this->resource;
        }

        if ($this->resource instanceof Traversable) {
            return iterator_to_array($this->resource);
        }

        return [];
    }

    /**
     * Countable implementation
     */
    public function count(): int
    {
        $items = $this->extractItems();
        return is_countable($items) ? count($items) : 0;
    }

    /**
     * IteratorAggregate implementation
     */
    public function getIterator(): Traversable
    {
        $items = $this->extractItems();
        return new ArrayIterator(is_array($items) ? $items : iterator_to_array($items));
    }
}
