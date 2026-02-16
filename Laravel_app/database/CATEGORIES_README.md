# Arquitectura de Categorías Jerárquicas

## Estructura de tablas

### `categories`
- `id`: PK
- `parent_id`: FK nullable a `categories.id` (permite jerarquías)
- `name`: Nombre de la categoría
- `slug`: URL-friendly identifier
- `description`: Descripción opcional

### `products`
- `id`: PK
- `category_id`: FK a `categories.id`
- `name`, `slug`, `description`...

## Ejemplos de uso

### 1. Crear categorías jerárquicas

```php
// Categoría raíz
$electronics = Category::create([
    'name' => 'Electrónica',
    'slug' => 'electronica',
]);

// Subcategoría
$smartphones = Category::create([
    'parent_id' => $electronics->id,
    'name' => 'Smartphones',
    'slug' => 'smartphones',
]);

// Sub-subcategoría (3er nivel)
$iphone = Category::create([
    'parent_id' => $smartphones->id,
    'name' => 'iPhone',
    'slug' => 'iphone',
]);
```

### 2. Obtener categorías padre e hijas

```php
$category = Category::find(1);

// Obtener la categoría padre
$parent = $category->parent;

// Obtener subcategorías directas
$children = $category->children;

// Obtener todos los descendientes (recursivo)
$descendants = $category->descendants;

// Verificar si es raíz
if ($category->isRoot()) {
    // Es una categoría principal sin padre
}

// Verificar si tiene hijos
if ($category->hasChildren()) {
    // Tiene subcategorías
}
```

### 3. Obtener productos de una categoría

```php
$category = Category::find(1);

// Productos directamente en esta categoría
$products = $category->products;

// Obtener categoría de un producto
$product = Product::find(1);
$category = $product->category;
$parentCategory = $product->category->parent;
```

### 4. Queries avanzadas

```php
// Todas las categorías raíz (sin padre)
$rootCategories = Category::whereNull('parent_id')->get();

// Todas las subcategorías de una categoría
$electronics = Category::where('slug', 'electronica')->first();
$subcategories = $electronics->children;

// Breadcrumb (ruta de categoría completa)
function getBreadcrumb($category) {
    $breadcrumb = [$category->name];
    
    while ($category->parent) {
        $category = $category->parent;
        array_unshift($breadcrumb, $category->name);
    }
    
    return implode(' > ', $breadcrumb);
}

// Ejemplo: "Electrónica > Smartphones > iPhone"
```

### 5. Productos con categoría y jerarquía completa

```php
// Eager loading para optimizar queries
$products = Product::with('category.parent')->get();

foreach ($products as $product) {
    echo $product->name . " - " . getBreadcrumb($product->category);
}
```

## Para queries más avanzadas

Si necesitas queries recursivas más complejas (ej: "todos los productos bajo Electrónica incluyendo todas las subcategorías"), puedes instalar:

```bash
composer require staudenmeir/laravel-adjacency-list
```

Y en el modelo `Category`:

```php
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Category extends Model
{
    use HasRecursiveRelationships;
    
    // Ya tienes acceso a:
    // - $category->ancestors (todos los padres hasta la raíz)
    // - $category->descendants (todos los hijos recursivamente)
    // - $category->subtree (el nodo + todos sus descendientes)
}
```

## Migración

Para aplicar los cambios:

```bash
# Si es la primera vez
php artisan migrate --seed

# Si ya tenías tablas
php artisan migrate:fresh --seed
```
