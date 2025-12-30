# 🚀 Laravel API Archetype

Un arquetipo robusto y escalable para construir APIs RESTful en Laravel. Proporciona una estructura sólida con patrones de diseño, autenticación, validación y manejo de errores listos para usar.

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green.svg)

---

## 📋 Tabla de Contenidos

-   [Características](#-características)
-   [Requisitos](#-requisitos)
-   [Instalación](#-instalación)
-   [Estructura del Proyecto](#-estructura-del-proyecto)
-   [Guía de Uso](#-guía-de-uso)
    -   [Crear un Nuevo Recurso](#1-crear-un-nuevo-recurso-completo)
    -   [Modelo](#2-modelo)
    -   [Servicio](#3-servicio)
    -   [Controlador](#4-controlador)
    -   [Request de Validación](#5-request-de-validación)
    -   [Recursos API](#6-recursos-api)
    -   [Rutas](#7-rutas)
    -   [Migraciones](#8-migraciones)
-   [Sistema de Autenticación](#-sistema-de-autenticación)
-   [Sistema de Filtrado y Ordenamiento](#-sistema-de-filtrado-y-ordenamiento)
-   [Formato de Respuestas](#-formato-de-respuestas)
-   [Manejo de Errores](#-manejo-de-errores)
-   [Testing](#-testing)
-   [Deployment](#-deployment)
-   [Licencia](#-licencia)

---

## ✨ Características

| Característica           | Descripción                                          |
| ------------------------ | ---------------------------------------------------- |
| 🔐 **Autenticación**     | Sistema unificado (Login/Registro) en Service Layer  |
| 📦 **CRUD Genérico**     | Operaciones atómicas con transacciones DB            |
| 📋 **Auditoría**         | Trait Auditable (created_by, updated_by, deleted_by) |
| 🛡️ **Autorización**      | Policies integradas para control de acceso           |
| 🔍 **Filtrado Avanzado** | Filtros type-safe usando Enums (FilterType)          |
| ⚡ **Modernidad PHP**    | Uso de `readonly`, `match` y tipado estricto 8.2+    |
| ✅ **Validación**        | Sanitización automática y validación de headers      |
| 🛡️ **Manejo de Errores** | Excepciones globales formateadas a JSON              |
| 🧪 **Testing**           | Tests de Feature y Unit con >80% de cobertura        |
| 🗑️ **Soft Deletes**      | Eliminación suave integrada por defecto              |

---

## 📋 Requisitos

-   PHP 8.2 o superior
-   Composer
-   SQLite / MySQL / PostgreSQL
-   Node.js y NPM (opcional, para assets)

---

## 🛠️ Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/api-archetype.git mi-proyecto
cd mi-proyecto
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar el entorno

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurar base de datos

**SQLite (desarrollo rápido):**

```bash
touch database/database.sqlite
```

**MySQL/PostgreSQL:** Editar `.env` con las credenciales correspondientes.

### 5. Ejecutar migraciones

```bash
php artisan migrate
```

### 6. Iniciar el servidor

```bash
php artisan serve
```

La API estará disponible en `http://localhost:8000/api/v1/`

---

## 📁 Estructura del Proyecto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php          # Controlador base
│   │   ├── AuthController.php      # Autenticación (thin controller)
│   │   ├── UserController.php      # Gestión de usuario
│   │   └── TaskController.php      # Ejemplo CRUD con Policy
│   ├── Requests/
│   │   ├── ApiRequest.php          # Request base con sanitización
│   │   ├── AuthRequest.php         # Validación de auth
│   │   ├── UserRequest.php         # Validación de usuario
│   │   └── TaskRequest.php         # Ejemplo de validación
│   └── Resources/
│       ├── ApiResource.php         # Resource base
│       ├── ApiCollection.php       # Collection con paginación
│       ├── TaskResource.php        # Ejemplo resource
│       └── TaskCollection.php      # Ejemplo collection
├── Models/
│   ├── Model.php                   # Modelo base con hooks
│   ├── User.php                    # Modelo de usuario
│   └── Task.php                    # Ejemplo de modelo
├── Services/
│   ├── Service.php                 # Servicio base CRUD
│   ├── AuthService.php             # Lógica de autenticación
│   └── TaskService.php             # Ejemplo de servicio
├── Policies/
│   └── TaskPolicy.php              # Ejemplo de autorización
├── Events/
│   └── UserRegistered.php          # Evento de registro
├── Listeners/
│   ├── CreateInitialUserSettings.php
│   └── SendWelcomeEmail.php
└── Traits/
    ├── Auditable.php               # Tracking de usuarios (created_by...)
    └── ApiResponseFormatter.php    # Contrato estricto de respuesta

routes/
└── api.php                         # Rutas de la API

database/
├── migrations/                     # Migraciones
├── factories/                      # Factories para testing
└── seeders/                        # Seeders de datos

tests/
├── Feature/                        # Tests de integración
└── Unit/                           # Tests unitarios
```

---

## 📖 Guía de Uso

### 1. Crear un Nuevo Recurso Completo

Para crear un nuevo recurso (ejemplo: `Product`), necesitas crear los siguientes archivos:

```bash
# Crear modelo con migración y factory
php artisan make:model Product -mf

# Crear controlador
php artisan make:controller ProductController

# Crear request de validación
php artisan make:request ProductRequest

# Crear resource y collection
php artisan make:resource ProductResource
php artisan make:resource ProductCollection
```

### 2. Modelo

Extiende del modelo base para obtener soft deletes y hooks:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, Auditable;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'category_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    // Hooks disponibles (opcionales)
    protected function beforeCreate()
    {
        // Ejecutado antes de crear
    }

    protected function afterCreate()
    {
        // Ejecutado después de crear
    }

    protected function beforeUpdate()
    {
        // Ejecutado antes de actualizar
    }

    protected function afterUpdate()
    {
        // Ejecutado después de actualizar
    }

    protected function beforeDelete()
    {
        // Ejecutado antes de eliminar
    }

    protected function afterDelete()
    {
        // Ejecutado después de eliminar
    }

    // Relaciones
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
```

### 3. Servicio

Extiende del servicio base para obtener CRUD y filtrado:

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

    /**
     * Obtiene productos con filtros aplicados
     */
    public function getProducts(array $params): LengthAwarePaginator
    {
        $query = $this->model->query();
        $query = $this->getFilteredAndSorted($query, $params);

        return $this->getAll($params['page'], $params['per_page'], $query);
    }

    /**
     * Obtiene un producto por ID
     */
    public function getProduct(int $id): Product
    {
        return $this->getById($id);
    }

    /**
     * Crea un nuevo producto
     */
    public function createProduct(array $data): Product
    {
        return $this->create($data);
    }

    /**
     * Actualiza un producto existente
     */
    public function updateProduct(int $id, array $data): Product
    {
        return $this->update($id, $data);
    }

    /**
     * Elimina un producto
     */
    public function deleteProduct(int $id): bool
    {
        return $this->delete($id);
    }

    // ==========================================
    // Filtros personalizados (opcional)
    // ==========================================

    /**
     * Filtra por categoría
     */
    protected function filterByCategory(Builder $query, int $value): Builder
    {
        return $query->where('category_id', $value);
    }

    /**
     * Filtra por rango de precio
     */
    protected function filterByPriceRange(Builder $query, array $value): Builder
    {
        if (isset($value['min'])) {
            $query->where('price', '>=', $value['min']);
        }
        if (isset($value['max'])) {
            $query->where('price', '<=', $value['max']);
        }
        return $query;
    }

    /**
     * Filtra productos en stock
     */
    protected function filterByInStock(Builder $query, bool $value): Builder
    {
        return $value
            ? $query->where('stock', '>', 0)
            : $query->where('stock', '=', 0);
    }

    // ==========================================
    // Configuración de búsqueda global
    // ==========================================

    /**
     * Columnas para búsqueda global
     */
    protected function getGlobalSearchColumns(): array
    {
        return ['name', 'description'];
    }

    /**
     * Relaciones para búsqueda global (opcional)
     */
    protected function getGlobalSearchRelations(): array
    {
        return [
            'category' => ['name'],
        ];
    }
}
```

### 4. Controlador

Extiende del controlador base para obtener helpers de respuesta y parámetros:

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

    public function __construct(
        protected readonly ProductService $productService
    ) {}

    /**
     * Listar productos
     */
    public function index(Request $request): JsonResponse
    {
        $params = $this->getQueryParams($request);
        $products = $this->productService->getProducts($params);

        return $this->successResponse($products);
    }

    /**
     * Crear producto
     */
    public function store(ProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct($request->validated());

        return $this->successResponse($product, 'Producto creado correctamente', 201);
    }

    /**
     * Mostrar producto
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->productService->getProduct($id);

        return $this->successResponse($product);
    }

    /**
     * Actualizar producto
     */
    public function update(ProductRequest $request, int $id): JsonResponse
    {
        $product = $this->productService->updateProduct($id, $request->validated());

        return $this->successResponse($product, 'Producto actualizado correctamente');
    }

    /**
     * Eliminar producto
     */
    public function destroy(int $id): JsonResponse
    {
        $this->productService->deleteProduct($id);

        return $this->successResponse(null, 'Producto eliminado correctamente');
    }

    // ==========================================
    // Configuración de filtros y ordenamiento
    // ==========================================

    /**
     * Filtros permitidos en la URL
     */
    protected function getAllowedFilters(): array
    {
        return ['global', 'category', 'price_range', 'in_stock'];
    }

    /**
     * Campo de ordenamiento por defecto
     */
    protected function getDefaultSortField(): string
    {
        return 'created_at';
    }

    /**
     * Orden por defecto
     */
    protected function getDefaultSortOrder(): string
    {
        return 'desc';
    }
}
```

### 5. Request de Validación

Extiende de `ApiRequest` para sanitización automática:

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

        // Campos requeridos solo en creación
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
            'name.max' => 'El nombre no puede exceder los 255 caracteres',
            'price.required' => 'El precio es obligatorio',
            'price.min' => 'El precio debe ser mayor o igual a 0',
            'stock.min' => 'El stock no puede ser negativo',
            'category_id.exists' => 'La categoría seleccionada no existe',
        ];
    }
}
```

### 6. Recursos API

#### Resource (elemento individual)

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

#### Collection (lista paginada)

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

class ProductCollection extends ApiCollection
{
    // Ya incluye paginación automática del ApiCollection base
    // Solo sobrescribe si necesitas personalizar
}
```

### 7. Rutas

Agregar en `routes/api.php`:

```php
Route::prefix('v1')->group(function () {
    // Recurso público
    Route::apiResource('products', ProductController::class);

    // O con autenticación
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('products', ProductController::class);
    });
});
```

### 8. Migraciones

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

## 🔐 Sistema de Autenticación

El arquetipo usa **Laravel Sanctum** con un sistema unificado de login/registro.

### Endpoints

| Método   | Endpoint                 | Descripción        | Auth |
| -------- | ------------------------ | ------------------ | ---- |
| `POST`   | `/api/v1/auth`           | Login o Registro   | No   |
| `GET`    | `/api/v1/auth`           | Usuario actual     | Sí   |
| `DELETE` | `/api/v1/auth`           | Logout             | Sí   |
| `GET`    | `/api/v1/users/profile`  | Obtener perfil     | Sí   |
| `PUT`    | `/api/v1/users/profile`  | Actualizar perfil  | Sí   |
| `PUT`    | `/api/v1/users/password` | Cambiar contraseña | Sí   |

### Ejemplos de uso

#### Login/Registro

```bash
curl -X POST http://localhost:8000/api/v1/auth \
  -H "Content-Type: application/json" \
  -d '{"email": "usuario@ejemplo.com", "password": "password123"}'
```

**Respuesta exitosa:**

```json
{
    "success": true,
    "message": "Usuario autenticado exitosamente",
    "data": {
        "id": 1,
        "name": "Usuario",
        "email": "usuario@ejemplo.com",
        "token": "1|abc123..."
    }
}
```

#### Usar el token

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

## 🔍 Sistema de Filtrado y Ordenamiento

### Parámetros de consulta

| Parámetro    | Descripción                 | Ejemplo           |
| ------------ | --------------------------- | ----------------- |
| `global`     | Búsqueda en campos de texto | `?global=laptop`  |
| `sort_by`    | Campo para ordenar          | `?sort_by=price`  |
| `sort_order` | Dirección (asc/desc)        | `?sort_order=asc` |
| `page`       | Número de página            | `?page=2`         |
| `per_page`   | Resultados por página       | `?per_page=20`    |
| `[campo]`    | Filtro específico           | `?status=active`  |

### Ejemplos

```bash
# Búsqueda global
GET /api/v1/products?global=laptop

# Filtrar por estado
GET /api/v1/tasks?status=pendiente

# Ordenar por precio descendente
GET /api/v1/products?sort_by=price&sort_order=desc

# Paginación
GET /api/v1/products?page=2&per_page=20

# Combinado
GET /api/v1/products?global=laptop&category=1&sort_by=price&page=1&per_page=10
```

### Filtro de rango de fechas

```php
// En el controlador, agregar a getQueryParams si necesitas rango de fechas
protected function getQueryParams(Request $request): array
{
    $params = parent::getQueryParams($request);

    $params['date_range'] = [
        'start' => $request->query('date_from'),
        'end' => $request->query('date_to'),
    ];

    return $params;
}
```

---

## 📤 Formato de Respuestas

### Respuesta exitosa

```json
{
    "success": true,
    "message": "Operación exitosa",
    "data": {
        "id": 1,
        "name": "Producto",
        "price": 99.99
    }
}
```

### Respuesta con paginación (Estructura Unificada)

```json
{
    "success": true,
    "message": "Operación exitosa",
    "data": [
        { "id": 1, "name": "Producto 1" },
        { "id": 2, "name": "Producto 2" }
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

### Contrato de `successResponse()`

El método `successResponse()` en los controladores aplica un contrato estricto entre la lógica de dominio y la presentación. Acepta únicamente:

-   **LengthAwarePaginator**: Se transforma automáticamente usando `transformCollection()`.
-   **Model**: Se transforma automáticamente usando `transformResource()`.
-   **array**: Para payloads explícitos (ej. tokens). No usar para modelos o colecciones.
-   **bool**: Para respuestas simples de estado.
-   **null**: Para acciones sin retorno (ej. delete). No incluye la clave `data` en el JSON.

Cualquier otro tipo (incluyendo pasar directamente un `JsonResource` o `Collection`) lanzará una excepción de arquitectura.

### Respuesta de error

```json
{
    "success": false,
    "message": "Error de validación",
    "errors": {
        "name": ["El nombre es obligatorio"],
        "price": ["El precio debe ser mayor o igual a 0"]
    }
}
```

---

## 🛡️ Manejo de Errores

El trait `ApiResponseFormatter` maneja automáticamente los errores:

| Excepción                       | Código HTTP | Mensaje                |
| ------------------------------- | ----------- | ---------------------- |
| `ModelNotFoundException`        | 404         | Recurso no encontrado  |
| `AuthenticationException`       | 401         | Autenticación fallida  |
| `AuthorizationException`        | 403         | Autorización fallida   |
| `ValidationException`           | 422         | Error de validación    |
| `QueryException`                | 500         | Error en base de datos |
| `NotFoundHttpException`         | 404         | Ruta no encontrada     |
| `MethodNotAllowedHttpException` | 405         | Método no permitido    |
| `ThrottleRequestsException`     | 429         | Demasiadas solicitudes |

---

## 🧪 Testing

### Estructura de tests

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
        └── TaskServiceTest.php
```

### Ejecutar tests

```bash
# Todos los tests
php artisan test

# Tests específicos
php artisan test --filter=TaskControllerTest

# Con cobertura
php artisan test --coverage

# En paralelo
php artisan test --parallel
```

### Ejemplo de test

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
            'title' => 'Nueva tarea',
            'description' => 'Descripción',
            'status' => 'pendiente'
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/tasks', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', ['title' => 'Nueva tarea']);
    }
}
```

---

## 🚀 Deployment

### Preparación para producción

```bash
# Optimizar autoloader
composer install --optimize-autoloader --no-dev

# Cachear configuración
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ejecutar migraciones
php artisan migrate --force
```

### Variables de entorno importantes

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=nombre_bd
DB_USERNAME=usuario
DB_PASSWORD=contraseña

# Sanctum
SANCTUM_STATEFUL_DOMAINS=tu-dominio.com
```

### CI/CD con GitHub Actions

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

## 📄 Licencia

Este proyecto está licenciado bajo la **Licencia MIT**. Ver el archivo [LICENSE](LICENSE) para más detalles.

---

## 🤝 Contribución

1. Fork el repositorio
2. Crea tu rama (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -m 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

---

<div align="center">
  <strong>Construido con ❤️ usando Laravel</strong>
</div>
