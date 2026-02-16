# Sistema de Canales de Venta - Opción B (Profesional)

## 📐 Arquitectura

### Tablas

#### 1. `channels` (Catálogo de canales)
Almacena los diferentes canales de venta donde puedes publicar productos.

**Campos:**
- `id`: PK
- `name`: Nombre del canal (ej: "Google Shopping")
- `code`: Código único (ej: "google")
- `slug`: URL-friendly
- `description`: Descripción del canal
- `is_active`: Si el canal está activo
- `config`: JSON con configuración del canal (API URLs, campos requeridos, etc.)

**Datos iniciales:**
- Google Shopping (`google`)
- Meta Catalog / Facebook (`meta`)
- MercadoLibre (`meli`)

#### 2. `product_channels` (Tabla pivote)
Relaciona productos con canales y almacena datos específicos por canal.

**Campos:**
- `product_id`: FK a products
- `channel_id`: FK a channels
- `is_active`: Si el producto está activo en este canal
- `published_at`: Cuándo se publicó
- `custom_title`: Título personalizado para el canal (opcional)
- `custom_description`: Descripción personalizada (opcional)
- `custom_price`: Precio personalizado (opcional)
- `metadata`: JSON con datos específicos del canal
- `last_synced_at`: Última sincronización
- `external_id`: ID del producto en el canal externo
- `external_url`: URL del producto en el canal
- **UNIQUE(`product_id`, `channel_id`)** → Un producto solo puede estar una vez por canal

---

## 🚀 Ejemplos de Uso

### 1. Obtener los canales predefinidos

```php
use App\Models\Channel;

// Todos los canales
$channels = Channel::all();

// Solo canales activos
$activeChannels = Channel::active()->get();

// Obtener un canal por código
$google = Channel::findByCode('google');
$meta = Channel::findByCode('meta');
$meli = Channel::findByCode('meli');
```

---

### 2. Publicar un producto en un canal

```php
use App\Models\Product;
use App\Models\Channel;

$product = Product::find(1);
$googleChannel = Channel::findByCode('google');

// Opción A: Usando attach (relación many-to-many)
$product->channels()->attach($googleChannel->id, [
    'is_active' => true,
    'published_at' => now(),
    'custom_price' => 99.99, // Precio especial para Google
    'metadata' => [
        'gtin' => '1234567890123',
        'mpn' => 'MPN-ABC-123',
        'condition' => 'new',
        'availability' => 'in stock',
        'shipping_weight' => 2.5,
    ],
]);

// Opción B: Crear ProductChannel directamente
ProductChannel::create([
    'product_id' => $product->id,
    'channel_id' => $googleChannel->id,
    'is_active' => true,
    'published_at' => now(),
    'metadata' => [
        'gtin' => '1234567890123',
        'condition' => 'new',
    ],
]);
```

---

### 3. Publicar en múltiples canales a la vez

```php
$product = Product::find(1);

$channelsData = [
    'google' => [
        'is_active' => true,
        'custom_price' => 99.99,
        'metadata' => [
            'gtin' => '1234567890123',
            'condition' => 'new',
        ],
    ],
    'meta' => [
        'is_active' => true,
        'metadata' => [
            'availability' => 'in stock',
            'condition' => 'new',
        ],
    ],
    'meli' => [
        'is_active' => true,
        'custom_price' => 105.00, // Precio diferente para MercadoLibre
        'metadata' => [
            'listing_type' => 'gold_special',
            'warranty' => '12 meses',
        ],
    ],
];

foreach ($channelsData as $channelCode => $data) {
    $channel = Channel::findByCode($channelCode);
    
    $product->channels()->attach($channel->id, [
        'is_active' => $data['is_active'],
        'published_at' => now(),
        'custom_price' => $data['custom_price'] ?? null,
        'metadata' => $data['metadata'],
    ]);
}
```

---

### 4. Consultar productos de un canal

```php
$google = Channel::findByCode('google');

// Todos los productos en Google Shopping
$productsInGoogle = $google->products;

// Solo productos activos
$activeProducts = $google->products()
    ->wherePivot('is_active', true)
    ->get();

// Productos publicados
$publishedProducts = $google->products()
    ->wherePivotNotNull('published_at')
    ->get();

// Con datos del pivot
foreach ($productsInGoogle as $product) {
    echo $product->name;
    echo $product->pivot->custom_price; // precio personalizado
    echo $product->pivot->metadata; // array con metadata
}
```

---

### 5. Consultar canales de un producto

```php
$product = Product::find(1);

// Todos los canales del producto
$channels = $product->channels;

// Solo canales activos
$activeChannels = $product->activeChannels;

// Verificar si está en un canal específico
if ($product->isInChannel('google')) {
    echo "Este producto está en Google Shopping";
}

// Obtener datos de un canal específico
$googleData = $product->getChannelData('google');
if ($googleData) {
    echo "Título: " . $googleData->effective_title;
    echo "Precio: " . $googleData->effective_price;
    echo "Metadata: " . json_encode($googleData->metadata);
}
```

---

### 6. Usar títulos/precios personalizados (Effective Values)

```php
$productChannel = ProductChannel::find(1);

// Si tiene custom_title, lo usa; si no, usa product->name
echo $productChannel->effective_title;

// Si tiene custom_description, lo usa; si no, usa product->description
echo $productChannel->effective_description;

// Si tiene custom_price, lo usa; si no, usa product->price
echo $productChannel->effective_price;
```

---

### 7. Actualizar datos de un canal

```php
$product = Product::find(1);
$googleChannel = Channel::findByCode('google');

// Actualizar usando sync con datos
$product->channels()->updateExistingPivot($googleChannel->id, [
    'custom_price' => 89.99,
    'metadata' => [
        'gtin' => '9876543210123',
        'availability' => 'preorder',
    ],
]);

// O actualizar el ProductChannel directamente
$productChannel = $product->getChannelData('google');
$productChannel->update([
    'custom_title' => 'Título especial para Google',
    'last_synced_at' => now(),
]);

// Marcar como sincronizado
$productChannel->markAsSynced();
```

---

### 8. Quitar un producto de un canal

```php
$product = Product::find(1);
$googleChannel = Channel::findByCode('google');

// Desactivar (soft)
$product->channels()->updateExistingPivot($googleChannel->id, [
    'is_active' => false,
]);

// Eliminar completamente (detach)
$product->channels()->detach($googleChannel->id);

// Eliminar de todos los canales
$product->channels()->detach();
```

---

### 9. Queries avanzadas

```php
// Productos en múltiples canales
$productsInGoogleAndMeta = Product::whereHas('channels', function($q) {
    $q->where('code', 'google');
})->whereHas('channels', function($q) {
    $q->where('code', 'meta');
})->get();

// Productos activos en un canal específico
$activeInGoogle = Product::whereHas('activeChannels', function($q) {
    $q->where('code', 'google');
})->get();

// Productos con precio personalizado en Google
$withCustomPrice = Product::whereHas('channels', function($q) {
    $q->where('code', 'google')
      ->whereNotNull('custom_price');
})->get();

// Productos que necesitan sincronización (más de 7 días)
$needsSync = ProductChannel::published()
    ->where('last_synced_at', '<', now()->subDays(7))
    ->with('product', 'channel')
    ->get();
```

---

### 10. Usando Factories

```php
use App\Models\Product;
use App\Models\Channel;
use App\Models\ProductChannel;

// Crear producto con canales
$product = Product::factory()
    ->hasAttached(
        Channel::factory()->count(3),
        ['is_active' => true, 'published_at' => now()]
    )
    ->create();

// Crear ProductChannel con metadata específica
$googlePC = ProductChannel::factory()
    ->forGoogle()
    ->published()
    ->create([
        'product_id' => $product->id,
        'channel_id' => Channel::findByCode('google')->id,
    ]);

// Crear para los 3 canales iniciales
$google = Channel::findByCode('google');
$meta = Channel::findByCode('meta');
$meli = Channel::findByCode('meli');

ProductChannel::factory()->forGoogle()->create([
    'product_id' => $product->id,
    'channel_id' => $google->id,
]);

ProductChannel::factory()->forMeta()->create([
    'product_id' => $product->id,
    'channel_id' => $meta->id,
]);

ProductChannel::factory()->forMeli()->create([
    'product_id' => $product->id,
    'channel_id' => $meli->id,
]);
```

---

## 🎯 Casos de Uso Reales

### Caso 1: Sincronizar con Google Shopping

```php
$product = Product::find(1);
$googleData = $product->getChannelData('google');

if ($googleData && $googleData->is_active) {
    // Preparar datos para Google Merchant Center
    $feedData = [
        'id' => $product->id,
        'title' => $googleData->effective_title,
        'description' => $googleData->effective_description,
        'price' => $googleData->effective_price . ' USD',
        'link' => route('products.show', $product->slug),
        'image_link' => $product->images->first()?->url,
        'gtin' => $googleData->metadata['gtin'] ?? null,
        'mpn' => $googleData->metadata['mpn'] ?? null,
        'condition' => $googleData->metadata['condition'] ?? 'new',
        'availability' => $googleData->metadata['availability'] ?? 'in stock',
    ];
    
    // Enviar a Google API
    // GoogleMerchantService::sync($feedData);
    
    // Marcar como sincronizado
    $googleData->update([
        'last_synced_at' => now(),
        'external_id' => 'GOOGLE_ID_123',
    ]);
}
```

### Caso 2: Dashboard de canales

```php
$product = Product::with('channels')->find(1);

foreach ($product->channels as $channel) {
    echo "Canal: " . $channel->name . "\n";
    echo "  Activo: " . ($channel->pivot->is_active ? 'Sí' : 'No') . "\n";
    echo "  Precio: " . $channel->pivot->custom_price ?? $product->price . "\n";
    echo "  Publicado: " . ($channel->pivot->published_at ? 'Sí' : 'No') . "\n";
    echo "  Última sync: " . ($channel->pivot->last_synced_at ?? 'Nunca') . "\n";
}
```

---

## 🔄 Flujo típico de trabajo

1. **Crear producto** → `Product::create([...])`
2. **Publicar en canales** → `$product->channels()->attach($channelId, [...])`
3. **Personalizar por canal** → Actualizar `custom_title`, `custom_price`, `metadata`
4. **Sincronizar** → Enviar datos a API externa, marcar con `markAsSynced()`
5. **Monitorear** → Consultar `last_synced_at`, verificar `is_active`

---

## ✅ Ventajas de este diseño

1. **Escalable**: Añadir nuevos canales solo requiere un INSERT en `channels`
2. **Flexible**: Metadata JSON permite campos específicos por canal
3. **Auditable**: Timestamps de publicación y sincronización
4. **Performance**: Relaciones Eloquent optimizadas con eager loading
5. **Mantenible**: Lógica centralizada en modelos con métodos helper
6. **Testing**: Factories completos para crear datos de prueba

---

## 🔧 Comandos útiles

```bash
# Aplicar migraciones
php artisan migrate

# Refrescar y seedear
php artisan migrate:fresh --seed

# Verificar estructura
php artisan tinker
>>> Channel::all()
>>> Product::with('channels')->first()
```
