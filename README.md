# 🚀 Laravel API Archetype

A robust and scalable archetype for building RESTful APIs in Laravel. It provides a solid structure with ready-to-use design patterns, authentication, validation, and error handling.

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green.svg)

---

## 📋 Table of Contents

-   [Features](#-features)
-   [Requirements](#-requirements)
-   [Installation](#-installation)
-   [Project Structure](#-project-structure)
-   [Usage Guide](#-usage-guide)
    -   [Create a New Resource](#1-create-a-new-complete-resource)
    -   [Model](#2-model)
    -   [Service](#3-service)
    -   [Controller](#4-controller)
    -   [Validation Request](#5-validation-request)
    -   [API Resources](#6-api-resources)
    -   [Routes](#7-routes)
    -   [Migrations](#8-migrations)
-   [Authentication System](#-authentication-system)
-   [Filtering and Sorting System](#-filtering-and-sorting-system)
-   [Response Format](#-response-format)
-   [Error Handling](#-error-handling)
-   [Testing](#-testing)
-   [Deployment](#-deployment)
-   [License](#-license)

---

## ✨ Features

| Feature                  | Description                                          |
| ------------------------ | ---------------------------------------------------- |
| 🔐 **Authentication**    | Unified system (Login/Register) in Service Layer     |
| 🏗️ **Eloquent Focus**     | Direct use of Eloquent without redundant layers      |
| 📋 **Auditing**          | Auditable Trait (created_by, updated_by, deleted_by) |
| 🛡️ **Authorization**     | Integrated Policies for access control               |
| 🔍 **Model Filtering**   | Advanced filtering defined directly in Models         |
| ⚡ **Modern PHP**        | Use of `readonly`, `match` and strict typing 8.2+    |
| ✅ **Validation**        | Smart `SearchRequest` with automatic pass-through    |
| 🛡️ **Error Handling**    | Global exceptions formatted to JSON                  |
| 🧪 **Testing**           | Feature and Unit tests with >80% coverage            |
| 🗑️ **Soft Deletes**      | Integrated soft deletes by default                   |

---

## 📋 Requirements

-   PHP 8.2 or higher
-   Composer
-   SQLite / MySQL / PostgreSQL
-   Node.js and NPM (optional, for assets)

---

## 🛠️ Installation

### 1. Clone the repository

```bash
git clone https://github.com/your-username/api-archetype.git my-project
cd my-project
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure the environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure database

**SQLite (fast development):**

```bash
touch database/database.sqlite
```

**MySQL/PostgreSQL:** Edit `.env` with the corresponding credentials.

### 5. Run migrations

```bash
php artisan migrate
```

### 6. Start the server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api/v1/`

---

## 📁 Project Structure

```
app/
├── Builders/
│   └── BaseBuilder.php         # Custom Eloquent Builder for API utils
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php          # Base controller (thin)
│   │   ├── AuthController.php      # Authentication
│   │   └── TaskController.php      # Example CRUD
│   ├── Requests/
│   │   ├── ApiRequest.php          # Base request with sanitization
│   │   ├── SearchRequest.php       # Common search validation
│   │   └── TaskRequest.php         # Resource validation
│   └── Resources/
│       ├── ApiResource.php         # Base resource
│       └── TaskResource.php        # Example resource
├── Models/
│   ├── Model.php                   # Base model
│   ├── User.php                    # User model
│   ├── Task.php                    # Example model (Filterable)
│   └── Traits/
│       └── Filterable.php          # Powerfull filtering logic
├── Services/
│   ├── AuthService.php             # Complex Domain Logic
│   └── TaskService.php             # Explicit Service (No Base class)
├── Policies/
│   └── TaskPolicy.php              # Example authorization
├── Events/
│   └── UserRegistered.php          # Registration event
├── Listeners/
│   ├── CreateInitialUserSettings.php
│   └── SendWelcomeEmail.php
└── Traits/
    ├── Auditable.php               # User tracking (created_by...)
    └── ApiResponseFormatter.php    # Strict response contract

routes/
└── api.php                         # API Routes

database/
├── migrations/                     # Migrations
├── factories/                      # Testing factories
└── seeders/                        # Data seeders

tests/
├── Feature/                        # Integration tests
└── Unit/                           # Unit tests
```

---

## 📖 Usage Guide

### 1. Create a New Complete Resource

To create a new resource (e.g., `Product`), you need to create the following files:

```bash
# Create model with migration and factory
php artisan make:model Product -mf

# Create controller
php artisan make:controller ProductController

# Create validation request
php artisan make:request ProductRequest

# Create resource and collection
php artisan make:resource ProductResource
php artisan make:resource ProductCollection
```

### 2. Model

Extend the base model and use the `Filterable` trait to enable advanced querying:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Builders\BaseBuilder;
use App\Models\Traits\Filterable;
use App\Traits\Auditable;

class Product extends Model
{
    use Auditable, Filterable;

    /**
     * Use the custom builder for API utilities
     */
    public function newEloquentBuilder($query): BaseBuilder
    {
        return new BaseBuilder($query);
    }

    protected $fillable = ['name', 'price', 'category_id'];

    /**
     * Define allowed filters and their types
     */
    public function getFilters(): array
    {
        return [
            'name' => \App\Support\Query\FilterType::PARTIAL,
            'category_id' => \App\Support\Query\FilterType::EXACT,
        ];
    }

    /**
     * Define supported ranges (dates or numbers)
     */
    public function getRanges(): array
    {
        return [
            'price' => 'price', // ?price_min=10&price_max=100
        ];
    }

    /**
     * Default sorting
     */
    public function getDefaultSortField(): string
    {
        return 'name';
    }
}
```

### 3. Service

Services are explicit and direct. No base class magic. They inject the Model and focus on domain logic:

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(
        protected Product $model
    ) {}

    /**
     * Get products with filters applied using BaseBuilder utils
     */
    public function getProducts(array $params): LengthAwarePaginator
    {
        return $this->model
            ->filterAndSort($params)      // Uses Filterable trait
            ->paginateFromParams($params); // Uses BaseBuilder safety
    }

    public function createProduct(array $data): Product
    {
        return $this->model->create($data);
    }
    
    // ... other CRUD methods
}
```

### 4. Controller

Controllers are skinny. Use `SearchRequest` for index validation:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Services\ProductService;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(SearchRequest $request)
    {
        // Just pass validated data. The Model knows how to filter.
        $products = $this->productService->getProducts($request->validated());

        return $this->successResponse($products);
    }
}
```

### 5. Validation Request

Extend `ApiRequest` for automatic sanitization:

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
            'category_id' => 'exists:categories,id',
        ];

        // Required fields only on creation
        if ($this->isMethod('post')) {
            $rules['name'] = 'required|string|max:255';
            $rules['price'] = 'required|numeric|min:0';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'name.max' => 'Name cannot exceed 255 characters',
            'price.required' => 'Price is required',
            'price.min' => 'Price must be greater than or equal to 0',
            'stock.min' => 'Stock cannot be negative',
            'category_id.exists' => 'Selected category does not exist',
        ];
    }
}
```

### 6. API Resources

#### Resource (single item)

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
            'category_id' => $this->category_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
```

#### Collection (paginated list)

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

class ProductCollection extends ApiCollection
{
    // Already includes automatic pagination from base ApiCollection
    // Only override if you need customization
}
```

### 7. Routes

Add to `routes/api.php`:

```php
Route::prefix('v1')->group(function () {
    // Public resource
    Route::apiResource('products', ProductController::class);

    // Or with authentication
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('products', ProductController::class);
    });
});
```

### 8. Migrations

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
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
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

---

## 🔐 Authentication System

The archetype uses **Laravel Sanctum** with a unified login/register system.

### Endpoints

| Method   | Endpoint                 | Description        | Auth |
| -------- | ------------------------ | ------------------ | ---- |
| `POST`   | `/api/v1/auth`           | Login or Register  | No   |
| `GET`    | `/api/v1/auth`           | Current User       | Yes  |
| `DELETE` | `/api/v1/auth`           | Logout             | Yes  |
| `GET`    | `/api/v1/users/profile`  | Get Profile        | Yes  |
| `PUT`    | `/api/v1/users/profile`  | Update Profile     | Yes  |
| `PUT`    | `/api/v1/users/password` | Change Password    | Yes  |

### Usage Examples

#### Login/Register

```bash
curl -X POST http://localhost:8000/api/v1/auth \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password123"}'
```

**Success Response:**

```json
{
    "success": true,
    "message": "User authenticated successfully",
    "data": {
        "id": 1,
        "name": "User",
        "email": "user@example.com",
        "token": "1|abc123..."
    }
}
```

#### Use the Token

```bash
curl http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer 1|abc123..."
```

#### Logout

```bash
curl -X DELETE http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer 1|abc123..."
```

---

## 🔍 Filtering and Sorting System

### Query Parameters

| Parameter    | Description                 | Example           |
| ------------ | --------------------------- | ----------------- |
| `global`     | Search in text fields       | `?global=laptop`  |
| `sort_by`    | Field to sort by            | `?sort_by=price`  |
| `sort_order` | Direction (asc/desc)        | `?sort_order=asc` |
| `page`       | Page number                 | `?page=2`         |
| `per_page`   | Results per page            | `?per_page=20`    |
| `[field]`    | Specific filter             | `?status=active`  |

### Examples

```bash
# Global search
GET /api/v1/products?global=laptop

# Filter by status
GET /api/v1/tasks?status=pending

# Sort by price descending
GET /api/v1/products?sort_by=price&sort_order=desc

# Pagination
GET /api/v1/products?page=2&per_page=20

# Combined
GET /api/v1/products?global=laptop&category=1&sort_by=price&page=1&per_page=10
```

### Global Search

Search in multiple columns and relationships:

```php
// In Model
protected function getGlobalSearchColumns(): array {
    return ['name', 'sku'];
}

protected function getGlobalSearchRelations(): array {
    return ['brand' => ['name']];
}
```

Usage: `?global=apple` will search name and sku of product, AND the name of its brand.

### Date Range Filter

Automatically supported by the `SearchRequest` and `Filterable` trait.

1. Define it in the Model:
```php
public function getRanges(): array {
    return ['date' => 'created_at'];
}
```
2. Call it from the API:
   - `?date_start=2023-01-01&date_end=2023-12-31`
   - Also supports `min_price` / `max_price` syntax for numeric ranges.

---

## 📤 Response Format

### Success Response

```json
{
    "success": true,
    "message": "Successful operation",
    "data": {
        "id": 1,
        "name": "Product",
        "price": 99.99
    }
}
```

### Paginated Response (Unified Structure)

```json
{
    "success": true,
    "message": "Successful operation",
    "data": [
        { "id": 1, "name": "Product 1" },
        { "id": 2, "name": "Product 2" }
    ],
    "meta": {
        "pagination": {
            "total": 50,
            "count": 10,
            "per_page": 10,
            "current_page": 1,
            "total_pages": 5,
            "has_more_pages": true
        }
    },
    "links": {
        "first": ".../api/v1/products?page=1",
        "last": ".../api/v1/products?page=5",
        "prev": null,
        "next": ".../api/v1/products?page=2"
    }
}
```

### `successResponse()` Contract

The `successResponse()` method in controllers enforces a strict contract between domain logic and presentation. It accepts only:

-   **LengthAwarePaginator**: Automatically transformed using `transformCollection()`.
-   **Model**: Automatically transformed using `transformResource()`.
-   **array**: For explicit payloads (e.g., tokens). Do not use for models or collections.
-   **bool**: For simple status responses.
-   **null**: For actions without return (e.g., delete). Does not include the `data` key in JSON.

Any other type (including passing a `JsonResource` or `Collection` directly) will throw an architectural exception.

### Error Response

```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "name": ["Name is required"],
        "price": ["Price must be greater than or equal to 0"]
    }
}
```

---

## 🛡️ Error Handling

The `ApiResponseFormatter` trait automatically handles errors:

| Exception                       | HTTP Code | Message                |
| ------------------------------- | --------- | ---------------------- |
| `ModelNotFoundException`        | 404       | Resource not found     |
| `AuthenticationException`       | 401       | Authentication failed  |
| `AuthorizationException`        | 403       | Authorization failed   |
| `ValidationException`           | 422       | Validation error       |
| `QueryException`                | 500       | Database error         |
| `NotFoundHttpException`         | 404       | Route not found        |
| `MethodNotAllowedHttpException` | 405       | Method not allowed     |
| `ThrottleRequestsException`     | 429       | Too many requests      |

---

## 🧪 Testing

### Test Structure

```
tests/
├── Feature/
│   ├── Api/
│   │   └── TaskApiTest.php
│   ├── Auth/
│   │   └── AuthenticationTest.php
│   └── Events/
│       └── UserRegisteredTest.php
└── Unit/
    ├── Models/
    │   └── TaskTest.php
    ├── Requests/
    │   └── TaskRequestTest.php
    └── Services/
    │   └── TaskServiceTest.php
```

### Running Tests

```bash
# All tests
php artisan test

# Specific tests
php artisan test --filter=TaskControllerTest

# With coverage
php artisan test --coverage

# Parallel
php artisan test --parallel
```

### Test Example

```php
<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_can_list_tasks(): void
    {
        Task::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [['id', 'title', 'status']],
                    'meta' => ['pagination']
                ]
            ]);
    }

    public function test_can_create_task(): void
    {
        $data = [
            'title' => 'New task',
            'description' => 'Description',
            'status' => 'pending'
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/tasks', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', ['title' => 'New task']);
    }
}
```

---

## 🚀 Deployment

### Production Preparation

```bash
# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force
```

### Important Environment Variables

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=db_name
DB_USERNAME=user
DB_PASSWORD=password

# Sanctum
SANCTUM_STATEFUL_DOMAINS=your-domain.com
```

### CI/CD with GitHub Actions

```yaml
name: CI/CD

on:
    push:
        branches: [main]
    pull_request:
        branches: [main]

jobs:
    test:
        runs-on: ubuntu-latest
        steps:
            - uses: actions/checkout@v4

            - name: Setup PHP
              uses: shivammathur/setup-php@v2
              with:
                  php-version: "8.2"
                  extensions: mbstring, pdo, sqlite

            - name: Install Dependencies
              run: composer install --prefer-dist --no-progress

            - name: Run Tests
              run: php artisan test
```

---

## 📄 License

This project is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for more details.

---

## 🤝 Contribution

1. Fork the repository
2. Create your branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -m 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Open a Pull Request

---

<div align="center">
  <strong>Built with ❤️ using Laravel</strong>
</div>
