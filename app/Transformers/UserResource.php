<?php

namespace App\Transformers;

use NovaFlow\Core\ApiResource;

/**
 * UserResource Transformer
 * Transforms UserModel into standardized API response
 */
class UserResource extends ApiResource
{
    /**
     * Transform the user model into array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->model->id ?? null,
            'name' => $this->model->name ?? null,
            'email' => $this->model->email ?? null,
            'role' => $this->model->role ?? 'user',
            'status' => $this->model->status ?? 0,
            'created_at' => $this->model->created_at ?? null,
            'updated_at' => $this->model->updated_at ?? null,
        ];
    }
}
