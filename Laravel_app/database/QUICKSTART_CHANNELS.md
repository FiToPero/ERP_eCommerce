# 🚀 Quick Start - Sistema de Canales de Venta

## 📦 1. Aplicar migraciones

```bash
cd /home/fito/AAdocker/ERP_filament/Laravel_app

# Ejecutar migraciones (crea tablas + inserta los 3 canales automáticamente)
php artisan migrate

# O refrescar todo
php artisan migrate:fresh --seed
```

Esto creará:
- ✅ Tabla `channels` con Google, Meta y MercadoLibre pre-cargados
- ✅ Tabla `product_channels` (vacía inicialmente)
- ✅ Usuarios, categorías y productos de ejemplo
- ✅ Productos publicados en los 3 canales con metadata realista

---

## 📊 2. Ver el reporte de canales

```bash
# Reporte general
php artisan channels:report

# Ver detalles de un producto específico
php artisan channels:report --product=1
```

**Salida del reporte general:**
```
📊 REPORTE GENERAL DE CANALES DE VENTA

🔷 Canales disponibles:
┌──────────────────┬────────┬───────┬─────────┬────────────┬────────────┐
│ Canal            │ Código │ Total │ Activos │ Publicados │ Estado     │
├──────────────────┼────────┼───────┼─────────┼────────────┼────────────┤
│ Google Shopping  │ google │ 6     │ 6       │ 5          │ ✅ Activo  │
│ Meta Catalog     │ meta   │ 5     │ 5       │ 4          │ ✅ Activo  │
│ MercadoLibre     │ meli   │ 4     │ 4       │ 3          │ ✅ Activo  │
└──────────────────┴────────┴───────┴─────────┴────────────┴────────────┘

⭐ Top 5 productos con más canales:
┌────┬──────────────────────────────────────────┬──────────┬─────────────┐
│ ID │ Producto                                 │ Canales  │ Precio Base │
├────┼──────────────────────────────────────────┼──────────┼─────────────┤
│ 1  │ iPhone 15 Pro                           │ 3        │ $1,199.99   │
│ 2  │ Samsung Galaxy S24 Ultra                │ 3        │ $1,299.99   │
│ 3  │ MacBook Pro 14"                         │ 3        │ $1,999.99   │
└────┴──────────────────────────────────────────┴──────────┴─────────────┘

⚠️  Productos que necesitan sincronización: 2
```

---

## 🎯 3. Ejemplos de código

### Consultar canales
```php
use App\Models\Channel;

// Obtener canal por código
$google = Channel::findByCode('google');
$meta = Channel::findByCode('meta');
$meli = Channel::findByCode('meli');

// Todos los canales activos
$activeChannels = Channel::active()->get();
```

### Publicar producto en un canal
```php
use App\Models\Product;

$product = Product::find(1);
$google = Channel::findByCode('google');

// Opción 1: Attach con datos personalizados
$product->channels()->attach($google->id, [
    'is_active' => true,
    'published_at' => now(),
    'custom_price' => 99.99,
    'metadata' => [
        'gtin' => '1234567890123',
        'condition' => 'new',
        'availability' => 'in stock',
    ],
]);

// Opción 2: Crear ProductChannel directamente
ProductChannel::create([
    'product_id' => $product->id,
    'channel_id' => $google->id,
    'is_active' => true,
    'published_at' => now(),
    'metadata' => ['gtin' => '1234567890123'],
]);
```

### Consultar productos de un canal
```php
$google = Channel::findByCode('google');

// Todos los productos
$products = $google->products;

// Solo activos y publicados
$published = $google->products()
    ->wherePivot('is_active', true)
    ->wherePivotNotNull('published_at')
    ->get();

// Acceder a datos del pivot
foreach ($products as $product) {
    echo $product->pivot->custom_price;
    echo $product->pivot->metadata['gtin'];
}
```

### Consultar canales de un producto
```php
$product = Product::find(1);

// Todos los canales
$channels = $product->channels;

// Solo activos
$activeChannels = $product->activeChannels;

// Verificar si está en un canal
if ($product->isInChannel('google')) {
    echo "En Google Shopping";
}

// Obtener datos específicos del canal
$googleData = $product->getChannelData('google');
echo $googleData->effective_price; // Usa custom o default
echo $googleData->effective_title;
```

### Actualizar datos de canal
```php
$product = Product::find(1);

// Actualizar pivot
$product->channels()->updateExistingPivot($google->id, [
    'custom_price' => 89.99,
    'metadata' => ['availability' => 'preorder'],
]);

// O actualizar ProductChannel directamente
$pc = $product->getChannelData('google');
$pc->update(['custom_title' => 'Nuevo título']);
$pc->markAsSynced(); // Actualiza last_synced_at
```

### Eliminar de un canal
```php
$product = Product::find(1);

// Desactivar (soft)
$product->channels()->updateExistingPivot($google->id, [
    'is_active' => false,
]);

// Eliminar (hard)
$product->channels()->detach($google->id);
```

---

## 🔍 4. Queries útiles

```php
// Productos en múltiples canales
$inGoogleAndMeta = Product::whereHas('channels', fn($q) => 
    $q->where('code', 'google')
)->whereHas('channels', fn($q) => 
    $q->where('code', 'meta')
)->get();

// Productos con precio personalizado
$withCustomPrice = Product::whereHas('productChannels', fn($q) =>
    $q->whereNotNull('custom_price')
)->get();

// Productos que necesitan sincronización (>7 días)
$needsSync = ProductChannel::where('is_active', true)
    ->where(function($q) {
        $q->where('last_synced_at', '<', now()->subDays(7))
          ->orWhereNull('last_synced_at');
    })
    ->with('product', 'channel')
    ->get();

// Productos publicados en los últimos 30 días
$recent = ProductChannel::where('published_at', '>=', now()->subDays(30))
    ->with('product', 'channel')
    ->get();
```

---

## 📝 5. Testing con Tinker

```bash
php artisan tinker
```

```php
// Ver todos los canales
Channel::all()->pluck('name', 'code');

// Primer producto con sus canales
$p = Product::with('channels')->first();
$p->channels->pluck('name');

// Datos de Google para un producto
$pc = $p->getChannelData('google');
$pc->metadata;
$pc->effective_price;

// Crear nuevo ProductChannel
ProductChannel::factory()->forGoogle()->create([
    'product_id' => 1,
    'channel_id' => Channel::findByCode('google')->id,
]);
```

---

## 🎨 6. Personalizar el seeder

Edita `ProductChannelSeeder.php` para ajustar:

```php
// Porcentaje de productos publicados
$isPublished = fake()->boolean(85); // 85%

// Rango de precios personalizados
'custom_price' => fake()->randomFloat(2, 
    $product->price * 0.9,  // -10%
    $product->price * 1.1   // +10%
),

// Metadata específica por canal
'metadata' => [
    'gtin' => fake()->ean13(),
    // ... tus campos
],
```

---

## 📚 7. Documentación completa

- **[CHANNELS_USAGE.md](CHANNELS_USAGE.md)** - Guía completa del sistema
- **[PRODUCT_CHANNEL_SEEDER_EXAMPLE.md](PRODUCT_CHANNEL_SEEDER_EXAMPLE.md)** - Ejemplos de datos generados

---

## ✅ Checklist de implementación

- [x] Migraciones creadas (channels, product_channels)
- [x] Modelos con relaciones (Channel, ProductChannel, Product)
- [x] Factories completos con metadata realista
- [x] Seeder con los 3 canales + productos publicados
- [x] Comando de reporte (channels:report)
- [x] Documentación y ejemplos

---

## 🚀 Todo listo para usar

```bash
# 1. Migrar y seedear
php artisan migrate:fresh --seed

# 2. Ver reporte
php artisan channels:report

# 3. Ver detalles de un producto
php artisan channels:report --product=1

# 4. Empezar a usar en tu código
# (Ver ejemplos arriba)
```

¡Disfruta tu sistema de canales multi-marketplace! 🎉
