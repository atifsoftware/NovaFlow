<?php

namespace NovaFlow\Core;

/**
 * API Resource
 * Transform data for API responses
 */
class ApiResource
{
    protected $resource;
    protected array $relations = [];

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public static function make($resource): self
    {
        return new self($resource);
    }

    public function transform(): array
    {
        if (is_null($this->resource)) {
            return [];
        }

        if ($this->resource instanceof Model) {
            return $this->transformModel($this->resource);
        }

        if (is_array($this->resource)) {
            return array_map([$this, 'transformModel'], $this->resource);
        }

        return (array) $this->resource;
    }

    protected function transformModel(Model $model): array
    {
        $data = $model->toArray();

        foreach ($this->relations as $relation) {
            if (isset($data[$relation])) {
                continue;
            }
            $data[$relation] = $model->$relation ?? null;
        }

        return $data;
    }

    public function include(string ...$relations): self
    {
        $this->relations = array_merge($this->relations, $relations);
        return $this;
    }

    public function toJson(): string
    {
        return json_encode($this->transform(), JSON_UNESCAPED_UNICODE);
    }

    public static function collection($resources): self
    {
        return new self($resources);
    }
}

/**
 * API Response Builder with Resources
 */
class ApiResourceResponse
{
    public static function resource($data): ApiResource
    {
        return new ApiResource($data);
    }

    public static function collection($data): ApiResource
    {
        return new ApiResource($data);
    }

    public static function paginate($data, int $total, int $page, int $perPage): array
    {
        return [
            'data' => (new ApiResource($data))->transform(),
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => (int) ceil($total / $perPage),
                'has_more' => $page * $perPage < $total
            ]
        ];
    }
}