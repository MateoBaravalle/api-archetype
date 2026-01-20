# ⚡ Quick Start Guide

This guide will take you from zero to having your first endpoint working in less than 10 minutes.

---

## 🎯 Goal

Create an API to manage **Products** with full CRUD operations.

---

## Step 1: Create the files

Run the following commands:

```bash
# Create model with migration and factory
php artisan make:model Product -mf

# Note: The controller, request, resource and service are created manually
# following the archetype structure
```

---

## Step 2: Configure the migration

Edit `database/migrations/xxxx_create_products_table.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

Run the migration:

```bash
php artisan migrate
```

---

## Step 3: Create the Model

Create `app/Models/Product.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];
}
```

---

## Step 4: Create the Service

Create `app/Services/ProductService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService extends Service
{
    public function __construct()
    {
        parent::__construct(new Product());
    }

    public function getProducts(array $params): LengthAwarePaginator
    {
        $query = $this->model->query();
        $query = $this->getFilteredAndSorted($query, $params);

        return $this->getAll($params['page'], $params['per_page'], $query);
    }

    public function getProduct(int $id): Product
    {
        return $this->getById($id);
    }

    public function createProduct(array $data): Product
    {
        return $this->create($data);
    }

    public function updateProduct(int $id, array $data): Product
    {
        return $this->update($id, $data);
    }

    public function deleteProduct(int $id): bool
    {
        return $this->delete($id);
    }

    protected function filterByStatus(Builder $query, string $value): Builder
    {
        return $query->where('status', $value);
    }

    protected function getGlobalSearchColumns(): array
    {
        return ['name', 'description'];
    }
}
```

---

## Step 5: Create the Request

Create `app/Http/Requests/ProductRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

class ProductRequest extends ApiRequest
{
    public function rules(): array
    {
        $rules = [
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'price' => 'numeric|min:0',
            'stock' => 'integer|min:0',
            'status' => 'string|in:active,inactive',
        ];

        if ($this->isMethod('post')) {
            $rules['name'] = 'required|string|max:255';
            $rules['price'] = 'required|numeric|min:0';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name is required',
            'price.required' => 'The price is required',
            'price.min' => 'The price cannot be negative',
        ];
    }
}
```

---

## Step 6: Create the Resource

Create `app/Http/Resources/ProductResource.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

class ProductResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
```

Create `app/Http/Resources/ProductCollection.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

class ProductCollection extends ApiCollection
{
    // Inherits pagination from ApiCollection
}
```

---

## Step 7: Create the Controller

Create `app/Http/Controllers/ProductController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $params = $this->getQueryParams($request);
            $products = $this->productService->getProducts($params);

            return $this->successResponse($this->transformCollection($products));
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function store(ProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->createProduct($request->validated());

            return $this->successResponse(
                $this->transformResource($product),
                'Product created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $product = $this->productService->getProduct($id);

            return $this->successResponse($this->transformResource($product));
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function update(ProductRequest $request, int $id): JsonResponse
    {
        try {
            $product = $this->productService->updateProduct($id, $request->validated());

            return $this->successResponse(
                $this->transformResource($product),
                'Product updated successfully'
            );
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->productService->deleteProduct($id);

            return $this->successResponse(null, 'Product deleted successfully');
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    protected function getAllowedFilters(): array
    {
        return ['global', 'status'];
    }
}
```

---

## Step 8: Add the routes

Edit `routes/api.php`:

```php
use App\Http\Controllers\ProductController;

Route::prefix('v1')->group(function () {
    // ... existing routes ...
    
    Route::apiResource('products', ProductController::class);
});
```

---

## Step 9: Test!

Start the server:

```bash
php artisan serve
```

### Create a product

```bash
curl -X POST http://localhost:8000/api/v1/products \
  -H "Content-Type: application/json" \
  -d '{"name": "Laptop", "price": 999.99, "description": "Gaming Laptop"}'
```

### List products

```bash
curl http://localhost:8000/api/v1/products
```

### Filter and search

```bash
# Global search
curl "http://localhost:8000/api/v1/products?global=laptop"

# Filter by status
curl "http://localhost:8000/api/v1/products?status=active"

# Sort
curl "http://localhost:8000/api/v1/products?sort_by=price&sort_order=desc"
```

### Get a product

```bash
curl http://localhost:8000/api/v1/products/1
```

### Update a product

```bash
curl -X PUT http://localhost:8000/api/v1/products/1 \
  -H "Content-Type: application/json" \
  -d '{"price": 899.99}'
```

### Delete a product

```bash
curl -X DELETE http://localhost:8000/api/v1/products/1
```

---

## 🎉 Done!

You now have a complete CRUD API with:

- ✅ Automatic pagination
- ✅ Global search
- ✅ Custom filters
- ✅ Flexible sorting
- ✅ Data validation
- ✅ Standardized JSON responses
- ✅ Error handling
- ✅ Soft deletes

---

## Next steps

- Add authentication with `auth:sanctum` middleware
- Create tests for the new resource
- Add more custom filters
- Implement relationships with other models

