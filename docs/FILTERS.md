# 🔍 Filtering and Sorting System

Complete guide on how to use and extend the archetype's filtering system.

---

## Index

- [Basic Filters](#basic-filters)
- [Global Search](#global-search)
- [Custom Filters](#custom-filters)
- [Sorting](#sorting)
- [Date Ranges](#date-ranges)
- [Pagination](#pagination)

---

## Basic Filters

### Controller Configuration

Define allowed filters in your controller:

```php
class ProductController extends Controller
{
    protected function getAllowedFilters(): array
    {
        return ['global', 'status', 'category', 'price'];
    }
}
```

### Usage in URL

```bash
# Simple filter
GET /api/v1/products?status=active

# Multiple filters
GET /api/v1/products?status=active&category=electronics
```

### Default Behavior

By default, filters use `LIKE` for strings:

```php
// URL: ?name=laptop
// SQL: WHERE name LIKE '%laptop%'
```

---

## Global Search

### Configuration

In your service, define searchable columns:

```php
class ProductService extends Service
{
    protected function getGlobalSearchColumns(): array
    {
        return ['name', 'description', 'sku'];
    }
}
```

### Usage

```bash
# Search in all defined columns
GET /api/v1/products?global=laptop
```

### Search in Relations

You can search in relations using dot-notation for nested relations:

```php
protected function getGlobalSearchRelations(): array
{
    return [
        'category' => ['name', 'slug'], // Direct relation
        'brand' => ['name'],
        'user.profile' => ['address', 'phone'], // Nested relation: user -> profile -> fields
    ];
}
```

### Security and Smart Search Features

1. **Tokenized Search**: The search term is split into "tokens". If you search "Laptop Pro", the system will search specifically for records containing "Laptop" AND "Pro" (in any order and column), providing more relevant results.
2. **Schema Validation**: The system automatically verifies that basic filters correspond to real columns in the table, preventing SQL errors from invalid parameters.

---

## Custom Filters

### Create a Custom Filter

In your service (or model, using scopes), create a `filterBy{Field}` method. This method receives the filter value and **all parameters** as the second argument, allowing for complex conditional logic.

```php
class Product extends Model
{
    /**
     * Exact filter by status
     * Receives $value (filter value) and $params (all applied filters)
     */
    public function scopeFilterByStatus(Builder $query, string $value, array $params = []): Builder
    {
        // Example: If 'archived' is requested but 'include_archived' is not included, ignore
        if ($value === 'archived' && !($params['include_archived'] ?? false)) {
             return $query;
        }

        return $query->where('status', $value);
    }

    /**
     * Filter by price range
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
     * Filter by multiple categories
     */
    protected function filterByCategories(Builder $query, array $value): Builder
    {
        return $query->whereIn('category_id', $value);
    }

    /**
     * Boolean filter
     */
    protected function filterByInStock(Builder $query, bool $value): Builder
    {
        return $value 
            ? $query->where('stock', '>', 0)
            : $query->where('stock', '=', 0);
    }

    /**
     * Filter by relationship
     */
    protected function filterByBrand(Builder $query, string $value): Builder
    {
        return $query->whereHas('brand', function ($q) use ($value) {
            $q->where('slug', $value);
        });
    }
}
```

### Using Custom Filters

```bash
# Simple filter
GET /api/v1/products?status=active

# Array filter (depends on how you parse the URL)
GET /api/v1/products?categories[]=1&categories[]=2

# Boolean filter
GET /api/v1/products?in_stock=true

# Relationship filter
GET /api/v1/products?brand=apple
```

---

## Sorting

### Default Configuration

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

### Usage

```bash
# Sort by price ascending
GET /api/v1/products?sort_by=price&sort_order=asc

# Sort by date descending
GET /api/v1/products?sort_by=created_at&sort_order=desc
```

### Custom Sorting

In your service, create a `sortBy{Field}` method:

```php
class ProductService extends Service
{
    /**
     * Sort by popularity (calculated field)
     */
    protected function sortByPopularity(Builder $query, string $order): void
    {
        $query->withCount('orders')
              ->orderBy('orders_count', $order);
    }

    /**
     * Sort by category name
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

## Date Ranges

### Configuration

The base service includes support for date range filtering.

```php
class ProductService extends Service
{
    /**
     * Define the date column for the range filter
     */
    protected function getDateColumn(): string
    {
        return 'created_at'; // Default
    }
}
```

### Add Support in Controller

```php
class ProductController extends Controller
{
    protected function getQueryParams(Request $request): array
    {
        $params = parent::getQueryParams($request);
        
        // Add date range
        $params['date_range'] = [
            'start' => $request->query('date_from'),
            'end' => $request->query('date_to'),
        ];
        
        return $params;
    }
}
```

### Usage

```bash
# Products created after a date
GET /api/v1/products?date_from=2024-01-01

# Products created before a date
GET /api/v1/products?date_to=2024-12-31

# Full range
GET /api/v1/products?date_from=2024-01-01&date_to=2024-12-31
```

---

## Pagination

### Parameters

| Parameter | Description | Default |
|-----------|-------------|---------|
| `page` | Page number | 1 |
| `per_page` | Results per page | 10 |

### Usage

```bash
# First page, 10 results
GET /api/v1/products

# Second page
GET /api/v1/products?page=2

# 25 results per page
GET /api/v1/products?per_page=25

# Combined
GET /api/v1/products?page=3&per_page=20
```

### Response with Metadata

```json
{
  "success": true,
  "message": "Operation successful",
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

## Complete Example

### Controller

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

### Service

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

### Complete Request

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


