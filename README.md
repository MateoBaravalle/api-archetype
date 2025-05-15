# Laravel API Archetype

Este es un arquetipo base para APIs RESTful en Laravel que proporciona una estructura sólida y reutilizable para el desarrollo de aplicaciones.

## Características

- Estructura base para APIs RESTful
- Manejo genérico de operaciones CRUD
- Filtrado y ordenamiento flexible
- Manejo centralizado de excepciones
- Respuestas JSON estandarizadas
- Paginación integrada
- Capacidades de búsqueda global
- Autenticación con Laravel Sanctum
- Limitación de tasa de solicitudes
- Clases base para validación
- Transformadores de recursos unificados
- Sistema de eventos y listeners
- Tests automatizados
- Manejo de soft deletes
- Validación de datos con mensajes personalizados
- Sistema de autenticación completo con registro y login
- Manejo de roles y permisos
- Sistema de notificaciones
- Logging centralizado
- Caché integrado

## Estructura

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php (Base)
│   │   ├── AuthController.php (Autenticación)
│   │   ├── UserController.php (Gestión de Usuario)
│   │   └── TaskController.php (Ejemplo)
│   ├── Middleware/
│   │   └── ApiRateLimiter.php
│   ├── Requests/
│   │   ├── ApiRequest.php (Base)
│   │   ├── AuthRequest.php (Autenticación)
│   │   ├── UserRequest.php (Gestión de Usuario)
│   │   └── TaskRequest.php (Ejemplo)
│   └── Resources/
│       ├── ApiResource.php (Base)
│       ├── ApiCollection.php (Base)
│       ├── TaskResource.php (Ejemplo)
│       └── TaskCollection.php (Ejemplo)
├── Models/
│   ├── Model.php (Base)
│   ├── User.php
│   └── Task.php (Ejemplo)
├── Services/
│   ├── Service.php (Base)
│   ├── AuthService.php
│   └── TaskService.php (Ejemplo)
├── Events/
│   └── UserRegistered.php
├── Listeners/
│   └── CreateInitialUserSettings.php
├── Exceptions/
│   └── Handler.php
└── Traits/
    └── HasUuid.php

routes/
├── api.php
├── web.php
└── console.php

config/
└── sanctum.php

database/
└── migrations/
    ├── 2014_10_12_000000_create_users_table.php
    ├── 2014_10_12_100000_create_password_reset_tokens_table.php
    ├── 2019_12_14_000001_create_personal_access_tokens_table.php
    └── 2023_01_01_000001_create_tasks_table.php

tests/
├── Feature/           # Tests de integración
│   ├── Api/          # Tests de endpoints API
│   ├── Auth/         # Tests de autenticación
│   ├── Events/       # Tests de eventos
│   └── TaskControllerTest.php  # Tests del controlador de tareas
├── Unit/             # Tests unitarios
└── TestCase.php      # Clase base para tests
```

## Uso

### Controlador Base

El controlador base proporciona métodos para manejar solicitudes HTTP, respuestas JSON, excepciones y parámetros de consulta:

```php
class YourController extends Controller
{
    /**
     * Define los filtros permitidos para consultas
     */
    protected function getAllowedFilters(): array;

    /**
     * Obtiene el campo predeterminado para ordenamiento
     */
    protected function getDefaultSortField(): string;

    /**
     * Obtiene el orden predeterminado para ordenamiento
     */
    protected function getDefaultSortOrder(): string;

    /**
     * Extrae los parámetros de filtrado de la solicitud
     */
    protected function getFilterParams(Request $request): array;

    /**
     * Extrae los parámetros de ordenamiento de la solicitud
     */
    protected function getSortingParams(Request $request): array;

    /**
     * Extrae los parámetros de paginación de la solicitud
     */
    protected function getPaginationParams(Request $request): array;

    /**
     * Combina todos los parámetros de consulta en un único array
     */
    protected function getQueryParams(Request $request): array;
}
```

El controlador base utiliza los siguientes traits:

- `AuthorizesRequests`: Para autorización
- `ValidatesRequests`: Para validación de datos
- `ApiResponseFormatter`: Para formatear respuestas JSON

### Autenticación y Gestión de Usuario

El sistema está dividido en dos controladores principales:

1. `AuthController`: Maneja la autenticación
   - Registro de usuarios
   - Login
   - Logout
   - Obtención de usuario autenticado

2. `UserController`: Maneja la gestión del usuario
   - Obtención de perfil
   - Actualización de perfil
   - Cambio de contraseña

Las validaciones se manejan a través de:

1. `AuthRequest`: Validaciones para autenticación
   - Registro
   - Login

2. `UserRequest`: Validaciones para gestión de usuario
   - Actualización de perfil
   - Cambio de contraseña

Ejemplo de implementación:

```php
// AuthController
class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function store(AuthRequest $request)
    {
        // Lógica de autenticación
    }
}

// UserController
class UserController extends Controller
{
    public function updateProfile(UserRequest $request)
    {
        // Lógica de actualización de perfil
    }

    public function updatePassword(UserRequest $request)
    {
        // Lógica de cambio de contraseña
    }
}
```

### Servicio Base

```php
class YourService extends Service
{
    public function __construct()
    {
        parent::__construct(new YourModel());
    }

    protected function getSearchableTextColumns(): array
    {
        return ['name', 'description'];
    }

    protected function getGlobalFilterRelations(): array
    {
        return [
            'relation' => ['field1', 'field2']
        ];
    }
}
```

### Request de Validación

```php
class YourRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            // Otras reglas...
        ];
    }
}
```

### Recurso API

```php
class YourResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // Otros campos...
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
```

### Colección API

```php
class YourCollection extends ApiCollection
{
    // Ya incluye paginación y formateo estándar
}
```

## Ejemplo Completo: API de Tareas

El arquetipo incluye un ejemplo funcional completo de una API para gestionar tareas:

### Endpoints

- `GET /api/v1/tasks` - Listar tareas (con soporte para paginación, filtrado y ordenamiento)
- `POST /api/v1/tasks` - Crear tarea
- `GET /api/v1/tasks/{id}` - Obtener tarea por ID
- `PUT /api/v1/tasks/{id}` - Actualizar tarea
- `DELETE /api/v1/tasks/{id}` - Eliminar tarea

### Filtros Disponibles

- `?global=texto` - Busca en título y descripción (filtro global)
- `?status=pendiente` - Filtra por estado (pendiente, en_progreso, completada)
- `?priority=3` - Filtra por prioridad (1-5)
- `?sort_by=due_date` - Ordena por fecha de vencimiento
- `?sort_order=asc` - Orden ascendente o descendente
- `?page=2` - Número de página
- `?per_page=15` - Resultados por página

### Validación de Datos

El sistema incluye validación robusta con mensajes personalizados:

```php
public function rules(): array
{
    return [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'status' => 'string|in:pendiente,en_progreso,completada',
        'due_date' => 'nullable|date',
        'priority' => 'integer|min:1|max:5',
    ];
}

public function messages(): array
{
    return [
        'title.required' => 'El título es obligatorio',
        'title.max' => 'El título no puede exceder los 255 caracteres',
        'status.in' => 'El estado debe ser pendiente, en_progreso o completada',
        'priority.min' => 'La prioridad debe ser al menos 1',
        'priority.max' => 'La prioridad no puede ser mayor que 5',
        'due_date.date' => 'La fecha de vencimiento debe ser una fecha válida',
    ];
}
```

### Sistema de Eventos

El arquetipo incluye un sistema de eventos para manejar acciones asíncronas:

```php
// Evento
class UserRegistered
{
    public function __construct(public User $user)
    {
    }
}

// Listener
class CreateInitialUserSettings implements ShouldQueue
{
    public function handle(UserRegistered $event): void
    {
        // Lógica para crear configuraciones iniciales
    }
}
```

### Testing

El proyecto incluye una estructura completa de testing con:

### Estructura de Tests

```
tests/
├── Feature/           # Tests de integración
│   ├── Api/          # Tests de endpoints API
│   ├── Auth/         # Tests de autenticación
│   ├── Events/       # Tests de eventos
│   └── TaskControllerTest.php  # Tests del controlador de tareas
├── Unit/             # Tests unitarios
└── TestCase.php      # Clase base para tests
```

### Ejemplo de Test de Controlador

```php
class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private array $auth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auth = $this->createAuthenticatedUser();
    }

    public function test_can_list_tasks(): void
    {
        Task::factory()->count(3)->create();

        $response = $this->withHeaders($this->auth['headers'])
            ->getJson('/api/v1/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'status',
                            'priority',
                            'due_date',
                            'created_at',
                            'updated_at'
                        ]
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
            ]);
    }

    public function test_can_create_task_with_valid_data(): void
    {
        $taskData = [
            'title' => 'Nueva Tarea',
            'description' => 'Descripción de la tarea',
            'status' => 'pendiente'
        ];

        $response = $this->withHeaders($this->auth['headers'])
            ->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'priority',
                    'due_date',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('tasks', $taskData);
    }
}
```

### Características de Testing

- Tests de integración para endpoints API
- Tests de autenticación
- Tests de eventos
- Tests de validación
- Tests de soft deletes
- Tests de paginación
- Tests de filtrado y ordenamiento
- Tests de respuestas JSON
- Tests de base de datos
- Tests de autorización

### Ejecución de Tests

1. **Ejecutar Todos los Tests**

```bash
php artisan test
```

2. **Ejecutar Tests Específicos**

```bash
php artisan test --filter=TaskControllerTest
```

3. **Ejecutar Tests con Cobertura**

```bash
php artisan test --coverage-html coverage
```

4. **Ejecutar Tests en Paralelo**

```bash
php artisan test --parallel
```

### Configuración del Entorno de Testing

El entorno de testing está configurado para usar SQLite en memoria:

```xml
<!-- phpunit.xml -->
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
</php>
```

### Traits de Testing

- `RefreshDatabase`: Para refrescar la base de datos entre tests
- `WithoutMiddleware`: Para deshabilitar middleware en tests específicos
- `DatabaseMigrations`: Para ejecutar migraciones antes de cada test

## Autenticación con Sanctum

El sistema de autenticación está implementado usando Laravel Sanctum y proporciona las siguientes funcionalidades:

### Endpoints de Autenticación

| Método | Endpoint | Descripción | Parámetros |
|--------|----------|-------------|------------|
| POST | /api/v1/auth | Login/Registro | email, password |
| GET | /api/v1/auth | Obtener usuario | Bearer Token |
| DELETE | /api/v1/auth | Logout | Bearer Token |
| GET | /api/v1/auth/profile | Obtener perfil | Bearer Token |
| PUT | /api/v1/auth/profile | Actualizar perfil | name, email |
| PUT | /api/v1/auth/password | Actualizar contraseña | password, password_confirmation |

### Ejemplo de Uso

```php
// Login/Registro
$response = $this->postJson('/api/v1/auth', [
    'email' => 'usuario@ejemplo.com',
    'password' => 'contraseña123'
]);

// Obtener perfil
$response = $this->withHeader('Authorization', 'Bearer ' . $token)
    ->getJson('/api/v1/auth/profile');

// Actualizar perfil
$response = $this->withHeader('Authorization', 'Bearer ' . $token)
    ->putJson('/api/v1/auth/profile', [
        'name' => 'Nuevo Nombre',
        'email' => 'nuevo@email.com'
    ]);

// Actualizar contraseña
$response = $this->withHeader('Authorization', 'Bearer ' . $token)
    ->putJson('/api/v1/auth/password', [
        'password' => 'nueva_contraseña',
        'password_confirmation' => 'nueva_contraseña'
    ]);

// Logout
$response = $this->withHeader('Authorization', 'Bearer ' . $token)
    ->deleteJson('/api/v1/auth');
```

### Características del Sistema de Autenticación

- Autenticación unificada para login y registro
- Manejo de tokens con Sanctum
- Validación de credenciales
- Actualización de perfil
- Cambio de contraseña
- Revocación de tokens
- Eventos de registro
- Respuestas JSON estandarizadas
- Manejo de errores centralizado

### Eventos

El sistema dispara eventos automáticamente:

- `UserRegistered`: Cuando un nuevo usuario se registra
- `UserLoggedIn`: Cuando un usuario inicia sesión
- `UserLoggedOut`: Cuando un usuario cierra sesión

### Middleware

Las rutas protegidas utilizan el middleware `auth:sanctum`:

```php
Route::middleware('auth:sanctum')->group(function () {
    // Rutas protegidas
});
```

## Configuración de Rutas

Las rutas API se definen en `routes/api.php`. Por defecto, todas las rutas están prefijadas con `/api/v1/`.

```php
Route::prefix('v1')->group(function () {
    // Recurso de tareas
    Route::apiResource('tasks', TaskController::class);
    
    // Ejemplo de rutas con middleware de autenticación
    Route::middleware('auth:sanctum')->group(function () {
        // Rutas protegidas
    });
});
```

## Instalación

1. Clona el repositorio:

```bash
git clone https://github.com/your-username/laravel-api-archetype.git
```

2. Instala las dependencias:

```bash
composer install
```

3. Configura el entorno:

```bash
cp .env.example .env
php artisan key:generate
```

4. Ejecuta las migraciones:

```bash
php artisan migrate
```

## Contribución

1. Fork el repositorio
2. Crea tu rama de características (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para más detalles.

## Guía de Inicio Rápido

### Requisitos Previos

- PHP 8.2 o superior
- Composer
- Node.js y NPM (para assets)
- SQLite (para desarrollo) o MySQL/PostgreSQL (para producción)

### Instalación

1. Clonar el repositorio:

```bash
git clone [url-del-repositorio]
cd [nombre-del-proyecto]
```

2. Instalar dependencias:

```bash
composer install
npm install
```

3. Configurar el entorno:

```bash
cp .env.example .env
php artisan key:generate
```

4. Configurar la base de datos:

```bash
# Para desarrollo con SQLite
touch database/database.sqlite
# O configurar MySQL/PostgreSQL en .env
```

5. Ejecutar migraciones:

```bash
php artisan migrate
```

6. Iniciar el servidor:

```bash
php artisan serve
```

## Casos de Uso Comunes

### 1. Crear un Nuevo Recurso API

1. Generar el modelo con migración:

```bash
php artisan make:model Product -m
```

2. Crear el controlador:

```bash
php artisan make:controller Api/V1/ProductController
```

3. Implementar el servicio:

```php
namespace App\Services;

class ProductService extends Service
{
    public function __construct()
    {
        parent::__construct(new Product());
    }

    protected function getSearchableTextColumns(): array
    {
        return ['name', 'description'];
    }
}
```

4. Definir el recurso API:

```php
namespace App\Http\Resources;

class ProductResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
```

### 2. Implementar Filtros Personalizados

```php
class ProductController extends Controller
{
    protected function getAllowedFilters(): array
    {
        return [
            'name' => 'like',
            'price' => 'between',
            'category_id' => 'equals',
            'status' => 'in'
        ];
    }
}
```

### 3. Manejo de Relaciones

```php
class ProductService extends Service
{
    protected function getGlobalFilterRelations(): array
    {
        return [
            'category' => ['name', 'slug'],
            'tags' => ['name']
        ];
    }
}
```

## Guía de Troubleshooting

### Problemas Comunes y Soluciones

1. **Error de Autenticación**
   - Verificar que el token está siendo enviado correctamente en el header
   - Comprobar que el token no ha expirado
   - Validar que el usuario existe y está activo

2. **Errores de Validación**
   - Revisar las reglas de validación en el Request correspondiente
   - Verificar que los datos enviados cumplen con el formato esperado
   - Comprobar los mensajes de error personalizados

3. **Problemas de Rate Limiting**
   - Verificar la configuración en `config/sanctum.php`
   - Comprobar los límites por IP y por usuario
   - Revisar los logs de rate limiting

### Logs y Debugging

1. **Habilitar Debug Mode**

```env
APP_DEBUG=true
```

2. **Ver Logs**

```bash
php artisan pail
# o
tail -f storage/logs/laravel.log
```

## Guía de Deployment

### Preparación para Producción

1. **Optimización**

```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
```

2. **Configuración de Servidor Web**

- Configurar Nginx/Apache
- Configurar SSL
- Configurar rate limiting a nivel de servidor

3. **Monitoreo**

- Configurar Laravel Telescope
- Implementar logging externo
- Configurar alertas

### CI/CD

Ejemplo de workflow de GitHub Actions:

```yaml
name: CI/CD

on:
  push:
    branches: [ main ]

jobs:
  tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install Dependencies
        run: composer install
      - name: Execute Tests
        run: php artisan test
```

## Integración con Servicios Externos

### 1. Integración con Servicios de Email

```php
// config/mail.php
return [
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST'),
            'port' => env('MAIL_PORT'),
            'encryption' => env('MAIL_ENCRYPTION'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
        ],
    ],
];
```

### 2. Integración con Servicios de Almacenamiento

```php
// config/filesystems.php
return [
    'disks' => [
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],
    ],
];
```

### 3. Integración con Servicios de Cola

```php
// config/queue.php
return [
    'default' => env('QUEUE_CONNECTION', 'redis'),
    'connections' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
        ],
    ],
];
```

## Mejores Prácticas

### 1. Seguridad

- Usar HTTPS en producción
- Implementar rate limiting
- Validar todas las entradas
- Sanitizar todas las salidas
- Usar prepared statements
- Implementar CORS correctamente

### 2. Performance

- Usar caché cuando sea posible
- Optimizar consultas a base de datos
- Implementar indexación adecuada
- Usar eager loading para relaciones
- Implementar paginación

### 3. Mantenibilidad

- Seguir PSR-12
- Documentar el código
- Escribir tests
- Usar type hints
- Implementar logging

## Testing

### Estructura de Tests

```
tests/
├── Feature/           # Tests de integración
│   ├── Api/          # Tests de endpoints API
│   ├── Auth/         # Tests de autenticación
│   ├── Events/       # Tests de eventos
│   └── TaskControllerTest.php  # Tests del controlador de tareas
├── Unit/             # Tests unitarios
└── TestCase.php      # Clase base para tests
```

### Ejemplo de Test de Controlador

```php
class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private array $auth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auth = $this->createAuthenticatedUser();
    }

    public function test_can_list_tasks(): void
    {
        Task::factory()->count(3)->create();

        $response = $this->withHeaders($this->auth['headers'])
            ->getJson('/api/v1/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'status',
                            'priority',
                            'due_date',
                            'created_at',
                            'updated_at'
                        ]
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
            ]);
    }

    public function test_can_create_task_with_valid_data(): void
    {
        $taskData = [
            'title' => 'Nueva Tarea',
            'description' => 'Descripción de la tarea',
            'status' => 'pendiente'
        ];

        $response = $this->withHeaders($this->auth['headers'])
            ->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'priority',
                    'due_date',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('tasks', $taskData);
    }
}
```

### Características de Testing

- Tests de integración para endpoints API
- Tests de autenticación
- Tests de eventos
- Tests de validación
- Tests de soft deletes
- Tests de paginación
- Tests de filtrado y ordenamiento
- Tests de respuestas JSON
- Tests de base de datos
- Tests de autorización

### Ejecución de Tests

1. **Ejecutar Todos los Tests**

```bash
php artisan test
```

2. **Ejecutar Tests Específicos**

```bash
php artisan test --filter=TaskControllerTest
```

3. **Ejecutar Tests con Cobertura**

```bash
php artisan test --coverage-html coverage
```

4. **Ejecutar Tests en Paralelo**

```bash
php artisan test --parallel
```

### Configuración del Entorno de Testing

El entorno de testing está configurado para usar SQLite en memoria:

```xml
<!-- phpunit.xml -->
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
</php>
```

### Traits de Testing

- `RefreshDatabase`: Para refrescar la base de datos entre tests
- `WithoutMiddleware`: Para deshabilitar middleware en tests específicos
- `DatabaseMigrations`: Para ejecutar migraciones antes de cada test

## Ejemplos de Uso

### Crear un Nuevo Controlador

```php
class ProductController extends Controller
{
    protected function getAllowedFilters(): array
    {
        return ['category', 'price_range', 'in_stock'];
    }

    protected function getDefaultSortField(): string
    {
        return 'created_at';
    }
}
```

### Implementar un Nuevo Servicio

```php
class ProductService extends Service
{
    public function getProducts(array $params): LengthAwarePaginator
    {
        $query = $this->model->query();
        return $this->getFilteredAndSorted($query, $params);
    }
}
```

## API Endpoints

### Autenticación

| Método | Endpoint | Descripción | Parámetros |
|--------|----------|-------------|------------|
| POST | /api/v1/auth | Login/Registro | email, password |
| GET | /api/v1/auth | Obtener usuario | Bearer Token |
| DELETE | /api/v1/auth | Logout | Bearer Token |

### Tareas

| Método | Endpoint | Descripción | Parámetros |
|--------|----------|-------------|------------|
| GET | /api/v1/tasks | Listar tareas | page, per_page, sort_by, sort_order |
| POST | /api/v1/tasks | Crear tarea | title, description, status |
| GET | /api/v1/tasks/{id} | Obtener tarea | id |
| PUT | /api/v1/tasks/{id} | Actualizar tarea | id, title, description, status |
| DELETE | /api/v1/tasks/{id} | Eliminar tarea | id |

## Guía de Contribución

### Requisitos Previos

- PHP 8.2 o superior
- Composer
- MySQL 8.0 o superior
- Node.js 18 o superior (para desarrollo frontend)

### Configuración del Entorno

1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/api-archetype.git
cd api-archetype
```

2. Instalar dependencias

```bash
composer install
npm install
```

3. Configurar variables de entorno

```bash
cp .env.example .env
php artisan key:generate
```

4. Ejecutar migraciones

```bash
php artisan migrate
```

### Convenciones de Código

- Seguir PSR-12 para PHP
- Usar tipos estrictos en PHP
- Documentar todas las clases y métodos
- Escribir tests para nueva funcionalidad

### Proceso de Pull Request

1. Crear una rama para tu feature
2. Implementar los cambios
3. Escribir/actualizar tests
4. Asegurar que todos los tests pasen
5. Actualizar documentación si es necesario
6. Enviar pull request
