# 🔐 Authentication System

Complete guide to the authentication system with Laravel Sanctum.

---

## Index

- [General Description](#general-description)
- [Endpoints](#endpoints)
- [Login and Registration](#login-and-registration)
- [Protecting Routes](#protecting-routes)
- [Token Management](#token-management)
- [Events](#events)
- [Customization](#customization)

---

## General Description

The archetype uses **Laravel Sanctum** for token-based API authentication. The system has the following features:

- **Unified Login/Registration**: A single endpoint that registers new users or authenticates existing ones.
- **API Tokens**: Long-lived tokens for API access.
- **Profile Management**: Endpoints to update profile and password.
- **Events**: Event system for post-registration actions.

---

## Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| `POST` | `/api/v1/auth` | Login or Registration | ❌ |
| `GET` | `/api/v1/auth` | Get current user | ✅ |
| `DELETE` | `/api/v1/auth` | Logout (revoke tokens) | ✅ |
| `GET` | `/api/v1/users/profile` | Get profile | ✅ |
| `PUT` | `/api/v1/users/profile` | Update profile | ✅ |
| `PUT` | `/api/v1/users/password` | Change password | ✅ |

---

## Login and Registration

### New User Registration

```bash
curl -X POST http://localhost:8000/api/v1/auth \
  -H "Content-Type: application/json" \
  -d '{
    "email": "new@user.com",
    "password": "password123"
  }'
```

**Response (201 Created):**

```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "id": 1,
    "name": null,
    "email": "new@user.com",
    "token": "1|abc123xyz789..."
  }
}
```

### Existing User Login

```bash
curl -X POST http://localhost:8000/api/v1/auth \
  -H "Content-Type: application/json" \
  -d '{
    "email": "existing@user.com",
    "password": "password123"
  }'
```

**Successful Response (200 OK):**

```json
{
  "success": true,
  "message": "User authenticated successfully",
  "data": {
    "id": 1,
    "name": "John",
    "email": "existing@user.com",
    "token": "2|def456uvw012..."
  }
}
```

**Response with incorrect credentials (422):**

```json
{
  "success": false,
  "message": "The provided credentials are incorrect.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

---

## Protecting Routes

### Authentication Middleware

```php
// routes/api.php

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/auth', [AuthController::class, 'store']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth', [AuthController::class, 'show']);
        Route::delete('/auth', [AuthController::class, 'destroy']);
        
        // Your protected resources
        Route::apiResource('products', ProductController::class);
    });
});
```

### Using the Token

Include the token in the `Authorization` header:

```bash
curl http://localhost:8000/api/v1/products \
  -H "Authorization: Bearer 1|abc123xyz789..."
```

### Getting User in Controller

```php
public function index(Request $request)
{
    $user = $request->user();
    
    // Filter by current user
    $products = $user->products;
}
```

---

## Token Management

### Get Current User

```bash
curl http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer 1|abc123xyz789..."
```

**Response:**

```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": {
    "id": 1,
    "name": "John",
    "email": "john@example.com"
  }
}
```

### Logout (Revoke Tokens)

```bash
curl -X DELETE http://localhost:8000/api/v1/auth \
  -H "Authorization: Bearer 1|abc123xyz789..."
```

**Response:**

```json
{
  "success": true,
  "message": "Session closed successfully",
  "data": null
}
```

---

## User Profile

### Get Profile

```bash
curl http://localhost:8000/api/v1/users/profile \
  -H "Authorization: Bearer 1|abc123..."
```

### Update Profile

```bash
curl -X PUT http://localhost:8000/api/v1/users/profile \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "new@email.com"
  }'
```

### Change Password

```bash
curl -X PUT http://localhost:8000/api/v1/users/password \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Content-Type: application/json" \
  -d '{
    "password": "new_password",
    "password_confirmation": "new_password"
  }'
```

---

## Events

### UserRegistered

It is automatically dispatched when a new user registers:

```php
// app/Events/UserRegistered.php
class UserRegistered
{
    public function __construct(public User $user)
    {
    }
}
```

### Available Listeners

```php
// app/Listeners/CreateInitialUserSettings.php
class CreateInitialUserSettings implements ShouldQueue
{
    public function handle(UserRegistered $event): void
    {
        // Create initial user settings
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

### Register Listeners

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

## Customization

### Add Fields to Registration

1. **Update AuthRequest:**

```php
class AuthRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20', // New field
        ];
    }
}
```

2. **Update AuthService:**

```php
public function createUser(array $data): User
{
    return User::create([
        'name' => $data['name'] ?? null,
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
        'phone' => $data['phone'] ?? null, // New field
    ]);
}
```

3. **Update User Model:**

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'phone', // New field
];
```

### Customize Token

```php
// In AuthService
public function createApiToken(User $user, string $name): string
{
    // Token with specific abilities
    return $user->createToken($name, ['read', 'write'])->plainTextToken;
}
```

### Validate Token Abilities

```php
// In the controller
public function destroy(int $id)
{
    if (!$request->user()->tokenCan('delete')) {
        return $this->errorResponse('You do not have permission to delete', 403);
    }
    
    // ...
}
```

### Token Expiration

```php
// config/sanctum.php
'expiration' => 60 * 24 * 7, // 7 days in minutes
```

---

## Security

### Recommendations

1. **HTTPS**: Always use HTTPS in production.
2. **Rate Limiting**: Already included in the archetype.
3. **Validation**: Passwords are validated with a minimum of 8 characters.
4. **Hashing**: Passwords are hashed automatically.

### Security Headers

Add in `app/Http/Middleware/SecurityHeaders.php`:

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

### Authentication Test

```php
public function test_user_can_register(): void
{
    $response = $this->postJson('/api/v1/auth', [
        'email' => 'new@test.com',
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

