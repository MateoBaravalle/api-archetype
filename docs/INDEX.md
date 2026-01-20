# 📚 API Archetype Documentation

Welcome to the complete documentation of the Laravel API Archetype.

---

## Available Guides

| Document | Description |
|-----------|-------------|
| [README](../README.md) | Main documentation and complete reference |
| [Quick Start](./QUICK_START.md) | Create your first resource in 10 minutes |
| [Filters](./FILTERS.md) | Filtering, search, and sorting system |
| [Authentication](./AUTHENTICATION.md) | Auth system with Laravel Sanctum |
| [Testing](./TESTING.md) | Complete testing guide |

---

## Recommended Path

### For new users

1. **[README](../README.md)** - Understand the general structure
2. **[Quick Start](./QUICK_START.md)** - Create your first resource
3. **[Authentication](./AUTHENTICATION.md)** - Protect your API

### For advanced development

1. **[Filters](./FILTERS.md)** - Master the filtering system
2. **[Testing](./TESTING.md)** - Write complete tests

---

## Project Structure

```
api-archetype/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Controllers
│   │   ├── Requests/           # Validation
│   │   └── Resources/          # Transformers
│   ├── Models/                 # Eloquent Models
│   ├── Services/               # Business Logic
│   ├── Events/                 # Events
│   ├── Listeners/              # Event Listeners
│   └── Traits/                 # Reusable Traits
├── routes/
│   └── api.php                 # API Routes
├── database/
│   ├── migrations/             # Migrations
│   └── factories/              # Testing Factories
├── tests/
│   ├── Feature/                # Integration Tests
│   └── Unit/                   # Unit Tests
└── docs/                       # This documentation
```

---

## Main Components

### Base Classes

| Class | Location | Purpose |
|-------|-----------|-----------|
| `Controller` | `app/Http/Controllers/Controller.php` | Base controller with helpers |
| `Model` | `app/Models/Model.php` | Base model with hooks and soft deletes |
| `Service` | `app/Services/Service.php` | Base CRUD Service |
| `ApiRequest` | `app/Http/Requests/ApiRequest.php` | Request with sanitization |
| `ApiResource` | `app/Http/Resources/ApiResource.php` | Base Resource |
| `ApiCollection` | `app/Http/Resources/ApiCollection.php` | Collection with pagination |

### Traits

| Trait | Purpose |
|-------|-----------|
| `ApiResponseFormatter` | JSON response formatting and error handling |

---

## Conventions

### File Names

- **Models**: `Product.php` (singular, PascalCase)
- **Controllers**: `ProductController.php`
- **Services**: `ProductService.php`
- **Requests**: `ProductRequest.php`
- **Resources**: `ProductResource.php`, `ProductCollection.php`
- **Migrations**: `2024_01_01_000001_create_products_table.php`
- **Tests**: `ProductApiTest.php`, `ProductServiceTest.php`

### Route Structure

```
/api/v1/products          GET     index
/api/v1/products          POST    store
/api/v1/products/{id}     GET     show
/api/v1/products/{id}     PUT     update
/api/v1/products/{id}     DELETE  destroy
```

### JSON Responses

```json
{
  "success": true|false,
  "message": "Descriptive message",
  "data": { ... } | null,
  "errors": { ... }  // Only on errors
}
```

---

## Support

If you have questions or find issues:

1. Check the corresponding documentation
2. Search in the repository issues
3. Open a new issue with details of the problem

---

<div align="center">
  <strong>Happy coding! 🚀</strong>
</div>

