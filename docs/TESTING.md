# 🧪 Guía de Testing

Guía completa para escribir y ejecutar tests en el arquetipo.

---

## Índice

- [Estructura de Tests](#estructura-de-tests)
- [Ejecutar Tests](#ejecutar-tests)
- [Tests de Controlador](#tests-de-controlador)
- [Tests de Servicio](#tests-de-servicio)
- [Tests de Request](#tests-de-request)
- [Tests de Modelo](#tests-de-modelo)
- [Helpers de Testing](#helpers-de-testing)
- [Factories](#factories)
- [Mocking](#mocking)

---

## Estructura de Tests

```
tests/
├── Feature/                    # Tests de integración
│   ├── Api/                    # Tests de endpoints
│   │   └── ProductApiTest.php
│   ├── Auth/                   # Tests de autenticación
│   │   └── AuthenticationTest.php
│   └── Events/                 # Tests de eventos
│       └── UserRegisteredTest.php
├── Unit/                       # Tests unitarios
│   ├── Models/
│   │   └── ProductTest.php
│   ├── Requests/
│   │   └── ProductRequestTest.php
│   └── Services/
│       └── ProductServiceTest.php
├── CreatesApplication.php
└── TestCase.php                # Clase base
```

---

## Ejecutar Tests

### Comandos básicos

```bash
# Ejecutar todos los tests
php artisan test

# Con verbose output
php artisan test -v

# Ejecutar un archivo específico
php artisan test tests/Feature/Api/ProductApiTest.php

# Ejecutar un test específico
php artisan test --filter=test_can_create_product

# Ejecutar tests de una clase
php artisan test --filter=ProductApiTest

# Ejecutar en paralelo (más rápido)
php artisan test --parallel

# Con cobertura de código
php artisan test --coverage

# Generar reporte HTML de cobertura
php artisan test --coverage-html coverage
```

### Usando PHPUnit directamente

```bash
./vendor/bin/phpunit

# Con configuración específica
./vendor/bin/phpunit --configuration phpunit.xml
```

---

## Tests de Controlador

### Estructura básica

```php
<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuario y token para autenticación
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    /**
     * Helper para requests autenticados
     */
    private function authHeaders(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }
}
```

### Test de listado (index)

```php
public function test_can_list_products(): void
{
    Product::factory()->count(5)->create();

    $response = $this->withHeaders($this->authHeaders())
        ->getJson('/api/v1/products');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'data' => [
                    '*' => ['id', 'name', 'price', 'created_at']
                ],
                'meta' => [
                    'pagination' => [
                        'total',
                        'count',
                        'per_page',
                        'current_page',
                        'total_pages',
                        'has_more_pages'
                    ]
                ]
            ]
        ])
        ->assertJsonPath('success', true);
}

public function test_can_filter_products(): void
{
    Product::factory()->create(['status' => 'active']);
    Product::factory()->create(['status' => 'inactive']);

    $response = $this->withHeaders($this->authHeaders())
        ->getJson('/api/v1/products?status=active');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data.data');
}

public function test_can_search_products(): void
{
    Product::factory()->create(['name' => 'Laptop Gaming']);
    Product::factory()->create(['name' => 'Mouse Wireless']);

    $response = $this->withHeaders($this->authHeaders())
        ->getJson('/api/v1/products?global=laptop');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data.data')
        ->assertJsonPath('data.data.0.name', 'Laptop Gaming');
}

public function test_can_paginate_products(): void
{
    Product::factory()->count(25)->create();

    $response = $this->withHeaders($this->authHeaders())
        ->getJson('/api/v1/products?page=2&per_page=10');

    $response->assertStatus(200)
        ->assertJsonPath('data.meta.pagination.current_page', 2)
        ->assertJsonPath('data.meta.pagination.per_page', 10)
        ->assertJsonCount(10, 'data.data');
}
```

### Test de creación (store)

```php
public function test_can_create_product(): void
{
    $data = [
        'name' => 'Nuevo Producto',
        'description' => 'Descripción del producto',
        'price' => 99.99,
        'stock' => 10
    ];

    $response = $this->withHeaders($this->authHeaders())
        ->postJson('/api/v1/products', $data);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'name', 'price']
        ])
        ->assertJsonPath('data.name', 'Nuevo Producto');

    $this->assertDatabaseHas('products', [
        'name' => 'Nuevo Producto',
        'price' => 99.99
    ]);
}

public function test_cannot_create_product_without_required_fields(): void
{
    $response = $this->withHeaders($this->authHeaders())
        ->postJson('/api/v1/products', []);

    $response->assertStatus(422)
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors(['name', 'price']);
}

public function test_cannot_create_product_with_invalid_data(): void
{
    $response = $this->withHeaders($this->authHeaders())
        ->postJson('/api/v1/products', [
            'name' => 'Test',
            'price' => -10 // Precio negativo
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['price']);
}
```

### Test de lectura (show)

```php
public function test_can_get_single_product(): void
{
    $product = Product::factory()->create();

    $response = $this->withHeaders($this->authHeaders())
        ->getJson("/api/v1/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $product->id)
        ->assertJsonPath('data.name', $product->name);
}

public function test_returns_404_for_nonexistent_product(): void
{
    $response = $this->withHeaders($this->authHeaders())
        ->getJson('/api/v1/products/99999');

    $response->assertStatus(404)
        ->assertJsonPath('success', false);
}
```

### Test de actualización (update)

```php
public function test_can_update_product(): void
{
    $product = Product::factory()->create(['name' => 'Original']);

    $response = $this->withHeaders($this->authHeaders())
        ->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Actualizado'
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Actualizado');

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Actualizado'
    ]);
}

public function test_can_partial_update_product(): void
{
    $product = Product::factory()->create([
        'name' => 'Original',
        'price' => 100
    ]);

    $response = $this->withHeaders($this->authHeaders())
        ->putJson("/api/v1/products/{$product->id}", [
            'price' => 150
        ]);

    $response->assertStatus(200);
    
    $product->refresh();
    $this->assertEquals('Original', $product->name);
    $this->assertEquals(150, $product->price);
}
```

### Test de eliminación (destroy)

```php
public function test_can_delete_product(): void
{
    $product = Product::factory()->create();

    $response = $this->withHeaders($this->authHeaders())
        ->deleteJson("/api/v1/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    // Verificar soft delete
    $this->assertSoftDeleted('products', ['id' => $product->id]);
}
```

---

## Tests de Servicio

```php
<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProductService();
    }

    public function test_can_get_products_with_pagination(): void
    {
        Product::factory()->count(15)->create();

        $params = [
            'page' => 1,
            'per_page' => 10,
            'sort_by' => 'created_at',
            'sort_order' => 'desc',
            'filters' => []
        ];

        $result = $this->service->getProducts($params);

        $this->assertEquals(15, $result->total());
        $this->assertEquals(10, $result->perPage());
        $this->assertCount(10, $result->items());
    }

    public function test_can_filter_by_status(): void
    {
        Product::factory()->count(3)->create(['status' => 'active']);
        Product::factory()->count(2)->create(['status' => 'inactive']);

        $params = [
            'page' => 1,
            'per_page' => 10,
            'sort_by' => 'created_at',
            'sort_order' => 'desc',
            'filters' => ['status' => 'active']
        ];

        $result = $this->service->getProducts($params);

        $this->assertEquals(3, $result->total());
    }

    public function test_can_create_product(): void
    {
        $data = [
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 10
        ];

        $product = $this->service->createProduct($data);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->name);
        $this->assertDatabaseHas('products', $data);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create(['name' => 'Original']);

        $updated = $this->service->updateProduct($product->id, [
            'name' => 'Updated'
        ]);

        $this->assertEquals('Updated', $updated->name);
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $result = $this->service->deleteProduct($product->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_throws_exception_for_nonexistent_product(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->getProduct(99999);
    }
}
```

---

## Tests de Request

```php
<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\ProductRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ProductRequestTest extends TestCase
{
    private function validate(array $data, string $method = 'POST'): array
    {
        $request = new ProductRequest();
        
        // Simular método HTTP
        $request->setMethod($method);
        
        $validator = Validator::make($data, $request->rules());
        
        return [
            'passes' => $validator->passes(),
            'errors' => $validator->errors()->toArray()
        ];
    }

    public function test_name_is_required_on_create(): void
    {
        $result = $this->validate(['price' => 100], 'POST');

        $this->assertFalse($result['passes']);
        $this->assertArrayHasKey('name', $result['errors']);
    }

    public function test_name_is_optional_on_update(): void
    {
        $result = $this->validate(['price' => 100], 'PUT');

        $this->assertTrue($result['passes']);
    }

    public function test_price_must_be_positive(): void
    {
        $result = $this->validate([
            'name' => 'Test',
            'price' => -10
        ], 'POST');

        $this->assertFalse($result['passes']);
        $this->assertArrayHasKey('price', $result['errors']);
    }

    public function test_valid_data_passes(): void
    {
        $result = $this->validate([
            'name' => 'Valid Product',
            'description' => 'A description',
            'price' => 99.99,
            'stock' => 10
        ], 'POST');

        $this->assertTrue($result['passes']);
    }
}
```

---

## Tests de Modelo

```php
<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_product(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'price' => 99.99
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product'
        ]);
    }

    public function test_soft_delete_works(): void
    {
        $product = Product::factory()->create();
        
        $product->delete();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertNull(Product::find($product->id));
        $this->assertNotNull(Product::withTrashed()->find($product->id));
    }

    public function test_fillable_attributes(): void
    {
        $product = new Product();

        $this->assertEquals(
            ['name', 'description', 'price', 'stock', 'status'],
            $product->getFillable()
        );
    }

    public function test_casts_attributes(): void
    {
        $product = Product::factory()->create([
            'price' => '99.99',
            'stock' => '10'
        ]);

        $this->assertIsFloat($product->price);
        $this->assertIsInt($product->stock);
    }
}
```

---

## Helpers de Testing

### TestCase base

```php
// tests/TestCase.php
<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Crear usuario autenticado con token
     */
    protected function createAuthenticatedUser(): array
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'headers' => ['Authorization' => "Bearer $token"]
        ];
    }

    /**
     * Hacer request autenticado
     */
    protected function authJson(string $method, string $uri, array $data = []): \Illuminate\Testing\TestResponse
    {
        $auth = $this->createAuthenticatedUser();
        
        return $this->withHeaders($auth['headers'])
            ->json($method, $uri, $data);
    }
}
```

### Usar los helpers

```php
public function test_example(): void
{
    // Opción 1: Usando createAuthenticatedUser
    $auth = $this->createAuthenticatedUser();
    
    $response = $this->withHeaders($auth['headers'])
        ->getJson('/api/v1/products');

    // Opción 2: Usando authJson
    $response = $this->authJson('GET', '/api/v1/products');
}
```

---

## Factories

### Definir factory

```php
// database/factories/ProductFactory.php
<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'stock' => fake()->numberBetween(0, 100),
            'status' => fake()->randomElement(['active', 'inactive']),
        ];
    }

    /**
     * Estado: producto activo
     */
    public function active(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'active'
        ]);
    }

    /**
     * Estado: sin stock
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attrs) => [
            'stock' => 0
        ]);
    }

    /**
     * Estado: precio alto
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attrs) => [
            'price' => fake()->randomFloat(2, 500, 5000)
        ]);
    }
}
```

### Usar factory

```php
// Crear un producto
$product = Product::factory()->create();

// Crear múltiples
$products = Product::factory()->count(10)->create();

// Con estado específico
$activeProducts = Product::factory()->active()->count(5)->create();

// Combinar estados
$expensiveActive = Product::factory()
    ->active()
    ->expensive()
    ->create();

// Sobrescribir atributos
$product = Product::factory()->create([
    'name' => 'Nombre Específico',
    'price' => 999.99
]);
```

---

## Mocking

### Mock de servicios

```php
use Mockery;

public function test_with_mocked_service(): void
{
    $mockService = Mockery::mock(ProductService::class);
    $mockService->shouldReceive('getProducts')
        ->once()
        ->andReturn(collect([]));

    $this->app->instance(ProductService::class, $mockService);

    $response = $this->authJson('GET', '/api/v1/products');

    $response->assertStatus(200);
}
```

### Mock de eventos

```php
use Illuminate\Support\Facades\Event;

public function test_event_is_dispatched(): void
{
    Event::fake();

    // Acción que dispara el evento
    $this->postJson('/api/v1/auth', [
        'email' => 'nuevo@test.com',
        'password' => 'password123'
    ]);

    Event::assertDispatched(UserRegistered::class);
}
```

### Mock de Mail

```php
use Illuminate\Support\Facades\Mail;

public function test_welcome_email_sent(): void
{
    Mail::fake();

    // Acción que envía email
    $this->postJson('/api/v1/auth', [
        'email' => 'nuevo@test.com',
        'password' => 'password123'
    ]);

    Mail::assertSent(WelcomeMail::class, function ($mail) {
        return $mail->hasTo('nuevo@test.com');
    });
}
```

---

## Configuración phpunit.xml

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
>
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app</directory>
        </include>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="APP_DEBUG" value="true"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
    </php>
</phpunit>
```

