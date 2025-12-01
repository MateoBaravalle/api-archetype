# 🔍 Sistema de Filtrado y Ordenamiento

Guía completa sobre cómo usar y extender el sistema de filtrado del arquetipo.

---

## Índice

- [Filtros Básicos](#filtros-básicos)
- [Búsqueda Global](#búsqueda-global)
- [Filtros Personalizados](#filtros-personalizados)
- [Ordenamiento](#ordenamiento)
- [Rango de Fechas](#rango-de-fechas)
- [Paginación](#paginación)

---

## Filtros Básicos

### Configuración en el Controlador

Define los filtros permitidos en tu controlador:

```php
class ProductController extends Controller
{
    protected function getAllowedFilters(): array
    {
        return ['global', 'status', 'category', 'price'];
    }
}
```

### Uso en la URL

```bash
# Filtro simple
GET /api/v1/products?status=active

# Múltiples filtros
GET /api/v1/products?status=active&category=electronics
```

### Comportamiento por defecto

Por defecto, los filtros usan `LIKE` para strings:

```php
// URL: ?name=laptop
// SQL: WHERE name LIKE '%laptop%'
```

---

## Búsqueda Global

### Configuración

En tu servicio, define las columnas buscables:

```php
class ProductService extends Service
{
    protected function getGlobalSearchColumns(): array
    {
        return ['name', 'description', 'sku'];
    }
}
```

### Uso

```bash
# Busca en todas las columnas definidas
GET /api/v1/products?global=laptop
```

### Búsqueda en Relaciones

```php
protected function getGlobalSearchRelations(): array
{
    return [
        'category' => ['name', 'slug'],
        'brand' => ['name'],
    ];
}
```

Esto buscará el término en las columnas de las relaciones también.

---

## Filtros Personalizados

### Crear un filtro personalizado

En tu servicio, crea un método `filterBy{Campo}`:

```php
class ProductService extends Service
{
    /**
     * Filtro exacto por status
     */
    protected function filterByStatus(Builder $query, string $value): Builder
    {
        return $query->where('status', $value);
    }

    /**
     * Filtro por rango de precios
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
     * Filtro por múltiples categorías
     */
    protected function filterByCategories(Builder $query, array $value): Builder
    {
        return $query->whereIn('category_id', $value);
    }

    /**
     * Filtro booleano
     */
    protected function filterByInStock(Builder $query, bool $value): Builder
    {
        return $value 
            ? $query->where('stock', '>', 0)
            : $query->where('stock', '=', 0);
    }

    /**
     * Filtro por relación
     */
    protected function filterByBrand(Builder $query, string $value): Builder
    {
        return $query->whereHas('brand', function ($q) use ($value) {
            $q->where('slug', $value);
        });
    }
}
```

### Uso de filtros personalizados

```bash
# Filtro simple
GET /api/v1/products?status=active

# Filtro con array (depende de cómo parsees la URL)
GET /api/v1/products?categories[]=1&categories[]=2

# Filtro booleano
GET /api/v1/products?in_stock=true

# Filtro por relación
GET /api/v1/products?brand=apple
```

---

## Ordenamiento

### Configuración por defecto

```php
class ProductController extends Controller
{
    protected function getDefaultSortField(): string
    {
        return 'created_at';
    }

    protected function getDefaultSortOrder(): string
    {
        return 'desc';
    }
}
```

### Uso

```bash
# Ordenar por precio ascendente
GET /api/v1/products?sort_by=price&sort_order=asc

# Ordenar por fecha descendente
GET /api/v1/products?sort_by=created_at&sort_order=desc
```

### Ordenamiento personalizado

En tu servicio, crea un método `sortBy{Campo}`:

```php
class ProductService extends Service
{
    /**
     * Ordenar por popularidad (campo calculado)
     */
    protected function sortByPopularity(Builder $query, string $order): void
    {
        $query->withCount('orders')
              ->orderBy('orders_count', $order);
    }

    /**
     * Ordenar por nombre de categoría
     */
    protected function sortByCategory(Builder $query, string $order): void
    {
        $query->join('categories', 'products.category_id', '=', 'categories.id')
              ->orderBy('categories.name', $order)
              ->select('products.*');
    }
}
```

---

## Rango de Fechas

### Configuración

El servicio base incluye soporte para filtrado por rango de fechas.

```php
class ProductService extends Service
{
    /**
     * Define la columna de fecha para el filtro de rango
     */
    protected function getDateColumn(): string
    {
        return 'created_at'; // Por defecto
    }
}
```

### Agregar soporte en el controlador

```php
class ProductController extends Controller
{
    protected function getQueryParams(Request $request): array
    {
        $params = parent::getQueryParams($request);
        
        // Agregar rango de fechas
        $params['date_range'] = [
            'start' => $request->query('date_from'),
            'end' => $request->query('date_to'),
        ];
        
        return $params;
    }
}
```

### Uso

```bash
# Productos creados después de una fecha
GET /api/v1/products?date_from=2024-01-01

# Productos creados antes de una fecha
GET /api/v1/products?date_to=2024-12-31

# Rango completo
GET /api/v1/products?date_from=2024-01-01&date_to=2024-12-31
```

---

## Paginación

### Parámetros

| Parámetro | Descripción | Default |
|-----------|-------------|---------|
| `page` | Número de página | 1 |
| `per_page` | Resultados por página | 10 |

### Uso

```bash
# Primera página, 10 resultados
GET /api/v1/products

# Segunda página
GET /api/v1/products?page=2

# 25 resultados por página
GET /api/v1/products?per_page=25

# Combinado
GET /api/v1/products?page=3&per_page=20
```

### Respuesta con metadatos

```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": {
    "data": [...],
    "meta": {
      "pagination": {
        "total": 150,
        "count": 10,
        "per_page": 10,
        "current_page": 1,
        "total_pages": 15,
        "has_more_pages": true
      }
    }
  }
}
```

---

## Ejemplo Completo

### Controlador

```php
class ProductController extends Controller
{
    protected function getAllowedFilters(): array
    {
        return [
            'global',
            'status',
            'category',
            'brand',
            'price_min',
            'price_max',
            'in_stock',
        ];
    }

    protected function getDefaultSortField(): string
    {
        return 'created_at';
    }

    protected function getDefaultSortOrder(): string
    {
        return 'desc';
    }

    protected function getQueryParams(Request $request): array
    {
        $params = parent::getQueryParams($request);
        
        $params['date_range'] = [
            'start' => $request->query('date_from'),
            'end' => $request->query('date_to'),
        ];
        
        return $params;
    }
}
```

### Servicio

```php
class ProductService extends Service
{
    protected function getGlobalSearchColumns(): array
    {
        return ['name', 'description', 'sku'];
    }

    protected function getGlobalSearchRelations(): array
    {
        return [
            'category' => ['name'],
            'brand' => ['name'],
        ];
    }

    protected function filterByStatus(Builder $query, string $value): Builder
    {
        return $query->where('status', $value);
    }

    protected function filterByCategory(Builder $query, int $value): Builder
    {
        return $query->where('category_id', $value);
    }

    protected function filterByBrand(Builder $query, int $value): Builder
    {
        return $query->where('brand_id', $value);
    }

    protected function filterByPriceMin(Builder $query, float $value): Builder
    {
        return $query->where('price', '>=', $value);
    }

    protected function filterByPriceMax(Builder $query, float $value): Builder
    {
        return $query->where('price', '<=', $value);
    }

    protected function filterByInStock(Builder $query, bool $value): Builder
    {
        return $value 
            ? $query->where('stock', '>', 0)
            : $query->where('stock', '=', 0);
    }
}
```

### Petición completa

```bash
curl "http://localhost:8000/api/v1/products?\
global=laptop&\
status=active&\
category=1&\
price_min=500&\
price_max=2000&\
in_stock=true&\
sort_by=price&\
sort_order=asc&\
page=1&\
per_page=20&\
date_from=2024-01-01"
```

