# NovaFlow API Resource Transformers

## 📋 Overview

API Resource Transformers provide a standardized way to transform your database models into consistent API responses. This is essential for mobile apps and third-party integrations.

## 🎯 Features

- **Standardized Response Format**: All API responses follow the same structure
- **Model Transformation**: Automatically convert models to clean arrays
- **Relationship Loading**: Include related models with `includes()`
- **Collection Support**: Transform multiple models at once
- **Pagination Support**: Built-in pagination response formatting
- **Metadata Injection**: Automatic timestamps and resource type info

## 📁 File Structure

```
app/
├── Transformers/
│   └── UserResource.php      # Example transformer
├── libraries/
│   ├── ApiResource.php       # Base transformer class
│   └── ApiController.php     # API controller base
routes/
├── api.php                   # API routes (modular)
├── web.php                   # Web routes (modular)
└── admin.php                 # Admin routes (modular)
```

## 🚀 Usage Guide

### 1. Create a Resource Transformer

```php
<?php
namespace App\Transformers;

use NovaFlow\Core\ApiResource;

class UserResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->model->id,
            'name' => $this->model->name,
            'email' => $this->model->email,
            'role' => $this->model->role ?? 'user',
            'created_at' => $this->model->created_at,
        ];
    }
}
```

### 2. Use in Controller (Single Resource)

```php
<?php
use NovaFlow\Core\ApiResourceResponse;
use App\Transformers\UserResource;
use App\Models\UserModel;

public function show($id)
{
    $user = UserModel::find($id);
    
    return $this->json(
        ApiResourceResponse::resource(new UserResource($user)),
        200
    );
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "created_at": "2024-01-01 00:00:00",
    "meta": {
      "transformed_at": "2024-01-01 12:00:00",
      "resource_type": "App\\Transformers\\UserResource"
    }
  }
}
```

### 3. Use in Controller (Collection)

```php
public function index()
{
    $users = UserModel::all();
    
    return $this->json(
        ApiResourceResponse::collection(
            new UserResource(null),
            null,
            $users
        ),
        200
    );
}
```

**Response:**
```json
{
  "status": "success",
  "data": [
    { /* user 1 */ },
    { /* user 2 */ }
  ],
  "meta": {
    "total": 10,
    "count": 10,
    "transformed_at": "2024-01-01 12:00:00",
    "resource_type": "App\\Transformers\\UserResource"
  }
}
```

### 4. With Relationships

```php
public function show($id)
{
    $user = UserModel::with('posts')->find($id);
    
    $resource = new UserResource($user);
    $resource->includes(['posts']);
    
    return $this->json(
        ApiResourceResponse::resource($resource),
        200
    );
}
```

### 5. Paginated Response

```php
public function index()
{
    $page = (int)($_GET['page'] ?? 1);
    $perPage = 15;
    
    $users = UserModel::query()
        ->limit($perPage)
        ->offset(($page - 1) * $perPage)
        ->get();
    
    $total = UserModel::count();
    
    return $this->json(
        ApiResourceResponse::paginate(
            $users,
            $total,
            $page,
            $perPage,
            UserResource::class
        ),
        200
    );
}
```

**Response:**
```json
{
  "status": "success",
  "data": [...],
  "pagination": {
    "total": 100,
    "per_page": 15,
    "current_page": 1,
    "total_pages": 7,
    "has_more": true
  },
  "meta": {
    "transformed_at": "2024-01-01 12:00:00"
  }
}
```

## 🔧 Modular Routing

Routes are now organized into separate files:

- `routes/web.php` - Frontend & Auth routes
- `routes/admin.php` - Admin panel routes (protected)
- `routes/api.php` - API routes (versioned)

### Example: Adding New API Route

```php
// routes/api.php
$router->group(['prefix' => 'api/v1'], function($router) {
    $router->get('/products', 'Api\V1\ProductController@index');
    $router->get('/products/{id}', 'Api\V1\ProductController@show');
});
```

## ✅ Benefits

1. **Consistency**: All API endpoints return the same format
2. **Maintainability**: Change response format in one place
3. **Scalability**: Easy to add new fields or relationships
4. **Mobile-Friendly**: Perfect for React Native, Flutter, iOS, Android
5. **Third-Party Ready**: Standard JSON structure for external integrations
6. **Type Safety**: Abstract base class enforces `toArray()` implementation

## 📝 Best Practices

- Always extend `ApiResource` for API responses
- Keep transformers focused on single models
- Use `includes()` for relationships
- Document response examples in controllers
- Version your API routes (`/api/v1`, `/api/v2`)
- Hide sensitive fields in Model's `$hidden` array

## 🎨 Example: Product Transformer

```php
<?php
namespace App\Transformers;

use NovaFlow\Core\ApiResource;

class ProductResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->model->id,
            'name' => $this->model->name,
            'price' => (float) $this->model->price,
            'stock' => (int) $this->model->stock,
            'image_url' => $this->model->image ? 
                '/uploads/' . $this->model->image : null,
            'category' => $this->model->category?->name,
            'is_available' => $this->model->stock > 0,
        ];
    }
}
```

---

**Created for NovaFlow Framework** ❤️  
Perfect for mobile apps and third-party integrations!
