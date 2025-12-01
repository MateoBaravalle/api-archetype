# 🔐 Sistema de Autenticación

Guía completa del sistema de autenticación con Laravel Sanctum.

---

## Índice

- [Descripción General](#descripción-general)
- [Endpoints](#endpoints)
- [Login y Registro](#login-y-registro)
- [Proteger Rutas](#proteger-rutas)
- [Gestión de Tokens](#gestión-de-tokens)
- [Eventos](#eventos)
- [Personalización](#personalización)

---

## Descripción General

El arquetipo utiliza **Laravel Sanctum** para autenticación API basada en tokens. El sistema tiene las siguientes características:

- **Login/Registro unificado**: Un solo endpoint que registra usuarios nuevos o autentica existentes
- **Tokens API**: Tokens de larga duración para acceso a la API
- **Gestión de perfil**: Endpoints para actualizar perfil y contraseña
- **Eventos**: Sistema de eventos para acciones post-registro

---

## Endpoints

| Método | Endpoint | Descripción | Auth Requerida |
|--------|----------|-------------|----------------|
| `POST` | `/api/v1/auth` | Login o Registro | ❌ |
| `GET` | `/api/v1/auth` | Obtener usuario actual | ✅ |
| `DELETE` | `/api/v1/auth` | Logout (revocar tokens) | ✅ |
| `GET` | `/api/v1/users/profile` | Obtener perfil | ✅ |
| `PUT` | `/api/v1/users/profile` | Actualizar perfil | ✅ |
| `PUT` | `/api/v1/users/password` | Cambiar contraseña | ✅ |

---

## Login y Registro

### Registro de nuevo usuario

```bash
curl -X POST http://localhost:8000/api/v1/auth \
  -H "Content-Type: application/json" \
  -d '{
    "email": "nuevo@usuario.com",
    "password": "password123"
  }'
```

**Respuesta (201 Created):**

```json
{
  "success": true,
  "message": "Usuario registrado exitosamente",
  "data": {
    "id": 1,
    "name": null,
    "email": "nuevo@usuario.com",
    "token": "1|abc123xyz789..."
  }
}
```

### Login de usuario existente

```bash
curl -X POST http://localhost:8000/api/v1/auth \
  -H "Content-Type: application/json" \
  -d '{
    "email": "existente@usuario.com",
    "password": "password123"
  }'
```

**Respuesta exitosa (200 OK):**

```json
{
  "success": true,
  "message": "Usuario autenticado exitosamente",
  "data": {
    "id": 1,
    "name": "Juan",
    "email": "existente@usuario.com",
    "token": "2|def456uvw012..."
  }
}
```

**Respuesta con credenciales incorrectas (422):**

```json
{
  "success": false,
  "message": "Las credenciales proporcionadas son incorrectas.",
  "errors": {
    "email": ["Las credenciales proporcionadas son incorrectas."]
  }
}
```

---

## Proteger Rutas

### Middleware de autenticación

```php
// routes/api.php

Route::prefix('v1')->group(function () {
    // Rutas públicas
    Route::post('/auth', [AuthController::class, 'store']);
    
    // Rutas protegidas
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth', [AuthController::class, 'show']);
        Route::delete('/auth', [AuthController::class, 'destroy']);
        
        // Tus recursos protegidos
        Route::apiResource('products', ProductController::class);
    });
});
```

### Usar el token

Incluye el token en el header `Authorization`:

```bash
curl http://localhost:8000/api/v1/products \
  -H "Authorization: Bearer 1|abc123xyz789..."
```

### Obtener usuario en el controlador

```php
public function index(Request $request)
{
    $user = $request->user();
    
    // Filtrar por usuario actual
    $products = $user->products;
}
```

---

## Gestión de Tokens

### Obtener usuario actual

```bash
curl http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer 1|abc123xyz789..."
```

**Respuesta:**

```json
{
  "success": true,
  "message": "Usuario obtenido exitosamente",
  "data": {
    "id": 1,
    "name": "Juan",
    "email": "juan@ejemplo.com"
  }
}
```

### Logout (revocar tokens)

```bash
curl -X DELETE http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer 1|abc123xyz789..."
```

**Respuesta:**

```json
{
  "success": true,
  "message": "Sesión cerrada exitosamente",
  "data": null
}
```

---

## Perfil de Usuario

### Obtener perfil

```bash
curl http://localhost:8000/api/v1/users/profile \
  -H "Authorization: Bearer 1|abc123..."
```

### Actualizar perfil

```bash
curl -X PUT http://localhost:8000/api/v1/users/profile \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Juan Pérez",
    "email": "nuevo@email.com"
  }'
```

### Cambiar contraseña

```bash
curl -X PUT http://localhost:8000/api/v1/users/password \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Content-Type: application/json" \
  -d '{
    "password": "nueva_contraseña",
    "password_confirmation": "nueva_contraseña"
  }'
```

---

## Eventos

### UserRegistered

Se dispara automáticamente cuando un nuevo usuario se registra:

```php
// app/Events/UserRegistered.php
class UserRegistered
{
    public function __construct(public User $user)
    {
    }
}
```

### Listeners disponibles

```php
// app/Listeners/CreateInitialUserSettings.php
class CreateInitialUserSettings implements ShouldQueue
{
    public function handle(UserRegistered $event): void
    {
        // Crear configuraciones iniciales del usuario
        $event->user->settings()->create([
            'notifications' => true,
            'theme' => 'light',
        ]);
    }
}

// app/Listeners/SendWelcomeEmail.php
class SendWelcomeEmail implements ShouldQueue
{
    public function handle(UserRegistered $event): void
    {
        Mail::to($event->user)->send(new WelcomeMail($event->user));
    }
}
```

### Registrar listeners

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    UserRegistered::class => [
        CreateInitialUserSettings::class,
        SendWelcomeEmail::class,
    ],
];
```

---

## Personalización

### Agregar campos al registro

1. **Actualizar AuthRequest:**

```php
class AuthRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20', // Nuevo campo
        ];
    }
}
```

2. **Actualizar AuthService:**

```php
public function createUser(array $data): User
{
    return User::create([
        'name' => $data['name'] ?? null,
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
        'phone' => $data['phone'] ?? null, // Nuevo campo
    ]);
}
```

3. **Actualizar modelo User:**

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'phone', // Nuevo campo
];
```

### Personalizar el token

```php
// En AuthService
public function createApiToken(User $user, string $name): string
{
    // Token con abilities específicas
    return $user->createToken($name, ['read', 'write'])->plainTextToken;
}
```

### Validar abilities del token

```php
// En el controlador
public function destroy(int $id)
{
    if (!$request->user()->tokenCan('delete')) {
        return $this->errorResponse('No tienes permiso para eliminar', 403);
    }
    
    // ...
}
```

### Expiración de tokens

```php
// config/sanctum.php
'expiration' => 60 * 24 * 7, // 7 días en minutos
```

---

## Seguridad

### Recomendaciones

1. **HTTPS**: Siempre usar HTTPS en producción
2. **Rate Limiting**: Ya incluido en el arquetipo
3. **Validación**: Las contraseñas se validan con mínimo 8 caracteres
4. **Hashing**: Las contraseñas se hashean automáticamente

### Headers de seguridad

Agregar en `app/Http/Middleware/SecurityHeaders.php`:

```php
public function handle($request, Closure $next)
{
    $response = $next($request);
    
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-Frame-Options', 'DENY');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    
    return $response;
}
```

---

## Testing

### Test de autenticación

```php
public function test_user_can_register(): void
{
    $response = $this->postJson('/api/v1/auth', [
        'email' => 'nuevo@test.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'email', 'token']
        ]);
}

public function test_user_can_login(): void
{
    $user = User::factory()->create([
        'password' => Hash::make('password123')
    ]);

    $response = $this->postJson('/api/v1/auth', [
        'email' => $user->email,
        'password' => 'password123'
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true);
}

public function test_user_cannot_login_with_wrong_password(): void
{
    $user = User::factory()->create();

    $response = $this->postJson('/api/v1/auth', [
        'email' => $user->email,
        'password' => 'wrong_password'
    ]);

    $response->assertStatus(422);
}

public function test_authenticated_user_can_logout(): void
{
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->deleteJson('/api/v1/auth');

    $response->assertStatus(200);
    $this->assertCount(0, $user->tokens);
}
```

