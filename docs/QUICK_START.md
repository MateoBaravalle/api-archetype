# ⚡ Guía de Inicio Rápido

Esta guía te llevará desde cero hasta tener tu primer endpoint funcionando en menos de 10 minutos.

---

## 🎯 Objetivo

Crear una API para gestionar **Productos** con operaciones CRUD completas.

---

## Paso 1: Crear los archivos

Ejecuta los siguientes comandos:

```bash
# Crear modelo con migración y factory
php artisan make:model Product -mf

# Nota: El controlador, request, resource y service se crean manualmente
# siguiendo la estructura del arquetipo
```

---

## Paso 2: Configurar la migración

Edita `database/migrations/xxxx_create_products_table.php`:

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

Ejecuta la migración:

```bash
php artisan migrate
```

---

## Paso 3: Crear el Modelo

Crea `app/Models/Product.php`:

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

## Paso 4: Crear el Servicio

Crea `app/Services/ProductService.php`:

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

## Paso 5: Crear el Request

Crea `app/Http/Requests/ProductRequest.php`:

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
            'name.required' => 'El nombre es obligatorio',
            'price.required' => 'El precio es obligatorio',
            'price.min' => 'El precio no puede ser negativo',
        ];
    }
}
```

---

## Paso 6: Crear el Resource

Crea `app/Http/Resources/ProductResource.php`:

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

Crea `app/Http/Resources/ProductCollection.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

class ProductCollection extends ApiCollection
{
    // Hereda la paginación del ApiCollection
}
```

---

## Paso 7: Crear el Controlador

Crea `app/Http/Controllers/ProductController.php`:

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
                'Producto creado correctamente',
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
                'Producto actualizado correctamente'
            );
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->productService->deleteProduct($id);

            return $this->successResponse(null, 'Producto eliminado correctamente');
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

## Paso 8: Agregar las rutas

Edita `routes/api.php`:

```php
use App\Http\Controllers\ProductController;

Route::prefix('v1')->group(function () {
    // ... rutas existentes ...
    
    Route::apiResource('products', ProductController::class);
});
```

---

## Paso 9: ¡Probar!

Inicia el servidor:

```bash
php artisan serve
```

### Crear un producto

```bash
curl -X POST http://localhost:8000/api/v1/products \
  -H "Content-Type: application/json" \
  -d '{"name": "Laptop", "price": 999.99, "description": "Laptop gaming"}'
```

### Listar productos

```bash
curl http://localhost:8000/api/v1/products
```

### Filtrar y buscar

```bash
# Búsqueda global
curl "http://localhost:8000/api/v1/products?global=laptop"

# Filtrar por status
curl "http://localhost:8000/api/v1/products?status=active"

# Ordenar
curl "http://localhost:8000/api/v1/products?sort_by=price&sort_order=desc"
```

### Obtener un producto

```bash
curl http://localhost:8000/api/v1/products/1
```

### Actualizar un producto

```bash
curl -X PUT http://localhost:8000/api/v1/products/1 \
  -H "Content-Type: application/json" \
  -d '{"price": 899.99}'
```

### Eliminar un producto

```bash
curl -X DELETE http://localhost:8000/api/v1/products/1
```

---

## 🎉 ¡Listo!

Ya tienes una API CRUD completa con:

- ✅ Paginación automática
- ✅ Búsqueda global
- ✅ Filtros personalizados
- ✅ Ordenamiento flexible
- ✅ Validación de datos
- ✅ Respuestas JSON estandarizadas
- ✅ Manejo de errores
- ✅ Soft deletes

---

## Próximos pasos

- Agregar autenticación con `auth:sanctum` middleware
- Crear tests para el nuevo recurso
- Agregar más filtros personalizados
- Implementar relaciones con otros modelos

