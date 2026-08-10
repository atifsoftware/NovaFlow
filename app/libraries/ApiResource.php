<?php

namespace NovaFlow\Core;

/**
 * API Resource Transformer
 * Transform models into standardized API responses
 * 
 * Usage Example:
 * class UserResource extends ApiResource {
 *     public function toArray($request): array {
 *         return [
 *             'id' => $this->model->id,
 *             'name' => $this->model->name,
 *             'email' => $this->model->email,
 *             'created_at' => $this->model->created_at,
 *         ];
 *     }
 * }
 * 
 * // In Controller:
 * return ApiResourceResponse::resource(new UserResource($user));
 */
abstract class ApiResource
{
    protected mixed $model;
    protected array $includes = [];

    public function __construct(mixed $model)
    {
        $this->model = $model;
    }

    /**
     * Transform the model into an array
     * Must be implemented by child classes
     */
    abstract public function toArray($request): array;

    /**
     * Add relationships to include
     */
    public function includes(array $relations): self
    {
        $this->includes = $relations;
        return $this;
    }

    /**
     * Load relationships
     */
    protected function loadIncludes(): void
    {
        if ($this->model instanceof Model && !empty($this->includes)) {
            $this->model->loadMany($this->includes);
        }
    }

    /**
     * Transform single model
     */
    public function transform($request = null): array
    {
        $this->loadIncludes();
        
        $data = $this->toArray($request ?? request());
        
        // Add metadata
        $data['meta'] = array_merge($data['meta'] ?? [], [
            'transformed_at' => date('Y-m-d H:i:s'),
            'resource_type' => static::class
        ]);

        return $data;
    }

    /**
     * Transform collection
     */
    public static function collection(array $models, $request = null): array
    {
        $resources = [];
        foreach ($models as $model) {
            $instance = new static($model);
            $resources[] = $instance->transform($request);
        }

        return [
            'data' => $resources,
            'meta' => [
                'total' => count($models),
                'count' => count($resources),
                'transformed_at' => date('Y-m-d H:i:s'),
                'resource_type' => static::class
            ]
        ];
    }

    /**
     * Additional metadata
     */
    protected function meta(): array
    {
        return [];
    }

    /**
     * Additional links
     */
    protected function links(): array
    {
        return [];
    }
}

/**
 * API Response Builder with Resources
 */
class ApiResourceResponse
{
    /**
     * Transform single resource
     */
    public static function resource(ApiResource $resource, $request = null): array
    {
        return [
            'status' => 'success',
            'data' => $resource->transform($request)
        ];
    }

    /**
     * Transform collection
     */
    public static function collection(ApiResource $resource, $request = null): array
    {
        $transformed = $resource->transform($request);
        return [
            'status' => 'success',
            'data' => $transformed['data'] ?? $transformed,
            'meta' => $transformed['meta'] ?? []
        ];
    }

    /**
     * Paginated response
     */
    public static function paginate(array $data, int $total, int $page, int $perPage, ?ApiResource $resource = null): array
    {
        $transformedData = $data;
        
        if ($resource && !empty($data)) {
            $transformedData = $resource::collection($data)['data'];
        }

        return [
            'status' => 'success',
            'data' => $transformedData,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => (int) ceil($total / $perPage),
                'has_more' => $page * $perPage < $total
            ],
            'meta' => [
                'transformed_at' => date('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Error response
     */
    public static function error(string $message, int $code = 400): array
    {
        return [
            'status' => 'error',
            'message' => $message,
            'code' => $code
        ];
    }
}