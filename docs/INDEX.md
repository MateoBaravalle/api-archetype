# 📚 Documentación del API Archetype

Bienvenido a la documentación completa del Laravel API Archetype.

---

## Guías Disponibles

| Documento | Descripción |
|-----------|-------------|
| [README](../README.md) | Documentación principal y referencia completa |
| [Quick Start](./QUICK_START.md) | Crear tu primer recurso en 10 minutos |
| [Filtros](./FILTERS.md) | Sistema de filtrado, búsqueda y ordenamiento |
| [Autenticación](./AUTHENTICATION.md) | Sistema de auth con Laravel Sanctum |
| [Testing](./TESTING.md) | Guía completa de testing |

---

## Recorrido Recomendado

### Para nuevos usuarios

1. **[README](../README.md)** - Entender la estructura general
2. **[Quick Start](./QUICK_START.md)** - Crear tu primer recurso
3. **[Autenticación](./AUTHENTICATION.md)** - Proteger tu API

### Para desarrollo avanzado

1. **[Filtros](./FILTERS.md)** - Dominar el sistema de filtrado
2. **[Testing](./TESTING.md)** - Escribir tests completos

---

## Estructura del Proyecto

```
api-archetype/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Controladores
│   │   ├── Requests/           # Validación
│   │   └── Resources/          # Transformadores
│   ├── Models/                 # Modelos Eloquent
│   ├── Services/               # Lógica de negocio
│   ├── Events/                 # Eventos
│   ├── Listeners/              # Manejadores de eventos
│   └── Traits/                 # Traits reutilizables
├── routes/
│   └── api.php                 # Rutas de la API
├── database/
│   ├── migrations/             # Migraciones
│   └── factories/              # Factories para testing
├── tests/
│   ├── Feature/                # Tests de integración
│   └── Unit/                   # Tests unitarios
└── docs/                       # Esta documentación
```

---

## Componentes Principales

### Clases Base

| Clase | Ubicación | Propósito |
|-------|-----------|-----------|
| `Controller` | `app/Http/Controllers/Controller.php` | Controlador base con helpers |
| `Model` | `app/Models/Model.php` | Modelo base con hooks y soft deletes |
| `Service` | `app/Services/Service.php` | Servicio base CRUD |
| `ApiRequest` | `app/Http/Requests/ApiRequest.php` | Request con sanitización |
| `ApiResource` | `app/Http/Resources/ApiResource.php` | Resource base |
| `ApiCollection` | `app/Http/Resources/ApiCollection.php` | Collection con paginación |

### Traits

| Trait | Propósito |
|-------|-----------|
| `ApiResponseFormatter` | Formateo de respuestas JSON y manejo de errores |

---

## Convenciones

### Nombres de archivos

- **Modelos**: `Product.php` (singular, PascalCase)
- **Controladores**: `ProductController.php`
- **Services**: `ProductService.php`
- **Requests**: `ProductRequest.php`
- **Resources**: `ProductResource.php`, `ProductCollection.php`
- **Migraciones**: `2024_01_01_000001_create_products_table.php`
- **Tests**: `ProductApiTest.php`, `ProductServiceTest.php`

### Estructura de rutas

```
/api/v1/products          GET     index
/api/v1/products          POST    store
/api/v1/products/{id}     GET     show
/api/v1/products/{id}     PUT     update
/api/v1/products/{id}     DELETE  destroy
```

### Respuestas JSON

```json
{
  "success": true|false,
  "message": "Mensaje descriptivo",
  "data": { ... } | null,
  "errors": { ... }  // Solo en errores
}
```

---

## Soporte

Si tienes preguntas o encuentras problemas:

1. Revisa la documentación correspondiente
2. Busca en los issues del repositorio
3. Abre un nuevo issue con detalles del problema

---

<div align="center">
  <strong>¡Feliz desarrollo! 🚀</strong>
</div>

