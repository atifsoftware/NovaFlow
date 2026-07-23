<?php

namespace App\Services;

use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\CartModel;

/**
 * ProductService
 * Handles business logic for products and categories.
 */
class ProductService
{
    protected int $perPage = 20;

    /**
     * Get paginated products
     */
    public function getPaginatedProducts(int $page, array $filters = [])
    {
        $offset = ($page - 1) * $this->perPage;
        
        return ProductModel::query()
            ->where('is_active', 1)
            ->limit($this->perPage)
            ->offset($offset)
            ->orderBy($this->getSortColumn($filters['sort'] ?? 'newest'), $this->getSortDir($filters['sort'] ?? 'newest'))
            ->get();
    }

    /**
     * Get total active products count
     */
    public function getTotalActiveCount(): int
    {
        return ProductModel::query()->where('is_active', 1)->count();
    }

    /**
     * Get categories for shop sidebar
     */
    public function getShopCategories()
    {
        return CategoryModel::getAllWithChildren();
    }

    /**
     * Get current cart count
     */
    public function getCartCount(): int
    {
        return CartModel::getCount();
    }

    /**
     * Helper for sort mapping
     */
    protected function getSortColumn(string $sort): string
    {
        return match($sort) {
            'price_asc', 'price_desc' => 'price',
            'popular'                 => 'sold_count',
            'rating'                  => 'rating_avg',
            default                   => 'created_at',
        };
    }

    protected function getSortDir(string $sort): string
    {
        return match($sort) {
            'price_asc' => 'ASC',
            default     => 'DESC',
        };
    }
}
