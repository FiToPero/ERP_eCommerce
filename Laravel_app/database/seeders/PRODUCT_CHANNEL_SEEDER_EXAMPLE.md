# ProductChannelSeeder - Ejemplo de Datos Generados

## 📋 Resumen

Este seeder toma los productos existentes y los publica en los 3 canales predefinidos:
- ✅ **Google Shopping** - 100% de productos
- ✅ **Meta/Facebook** - 80% de productos  
- ✅ **MercadoLibre** - 70% de productos

---

## 🎯 Ejemplo de datos generados

### Producto: iPhone 15 Pro

#### 1️⃣ Google Shopping
```php
ProductChannel {
    product_id: 1,
    channel_id: 1, // Google
    is_active: true,
    published_at: "2025-11-15 10:30:00",
    custom_title: null, // Usa el título del producto
    custom_price: null, // Usa el precio del producto (1199.99)
    metadata: [
        'gtin' => '0190199559574',
        'mpn' => 'MPN-1234AB',
        'brand' => 'Apple',
        'condition' => 'new',
        'availability' => 'in stock',
        'price' => '1199.99 USD',
        'shipping_weight' => 0.5,
        'shipping_weight_unit' => 'kg',
        'google_product_category' => 'Electronics > Computers & Accessories',
        'product_type' => 'Smartphones',
        'image_link' => 'https://example.com/iphone-15-pro.jpg',
        'additional_image_link' => [
            'https://example.com/iphone-15-pro-2.jpg',
            'https://example.com/iphone-15-pro-3.jpg',
        ]
    ],
    last_synced_at: "2026-02-10 14:20:00",
    external_id: "GS-AB123CD456",
    external_url: "https://merchants.google.com/product/uuid-here"
}
```

#### 2️⃣ Meta/Facebook Catalog
```php
ProductChannel {
    product_id: 1,
    channel_id: 2, // Meta
    is_active: true,
    published_at: "2025-12-01 09:15:00",
    custom_title: "iPhone 15 Pro - Último Modelo Disponible",
    custom_price: 1189.99, // 5% menor para Facebook
    metadata: [
        'availability' => 'in stock',
        'condition' => 'new',
        'price' => '1199.99 USD',
        'link' => 'https://mitienda.com/products/iphone-15-pro',
        'image_link' => 'https://example.com/iphone-15-pro.jpg',
        'brand' => 'Apple',
        'fb_product_category' => 'Electronics & Computers',
        'product_type' => 'Smartphones',
        'sale_price' => '1099.99 USD',
        'sale_price_effective_date' => '2026-03-15',
        'custom_label_0' => 'Bestseller',
        'custom_label_1' => 'Smartphones',
        'custom_label_2' => 'Premium',
    ],
    last_synced_at: "2026-02-12 08:45:00",
    external_id: "FB-1234567890",
    external_url: "https://www.facebook.com/commerce/products/uuid"
}
```

#### 3️⃣ MercadoLibre
```php
ProductChannel {
    product_id: 1,
    channel_id: 3, // MercadoLibre
    is_active: true,
    published_at: "2025-11-20 16:00:00",
    custom_title: "iPhone 15 Pro - Envío Gratis",
    custom_description: "✨ IPHONE 15 PRO ✨

iPhone 15 Pro con pantalla Super Retina XDR...

📦 ESPECIFICACIONES TÉCNICAS:
• Marca: Apple
• Modelo: A2848
• Color: Titanio Natural
• Peso: 0.187 kg

✅ GARANTÍA: 12 meses
🚚 ENVÍO: A todo México
💳 PAGOS: Aceptamos todos los métodos de pago

¡COMPRA CON CONFIANZA!",
    custom_price: 1439.99, // 20% más alto por comisiones de Meli
    metadata: [
        'listing_type' => 'gold_special',
        'category_id' => 'MLM1055', // Smartphones
        'price' => 1439.99,
        'currency_id' => 'MXN',
        'available_quantity' => 50,
        'buying_mode' => 'buy_it_now',
        'condition' => 'new',
        'warranty' => '12 meses de garantía del vendedor',
        'shipping' => [
            'mode' => 'me2',
            'free_shipping' => true,
            'local_pick_up' => false,
        ],
        'pictures' => [
            'https://example.com/iphone-15-pro.jpg',
            'https://example.com/iphone-15-pro-2.jpg',
        ],
        'video_id' => null,
        'attributes' => [
            ['id' => 'BRAND', 'value_name' => 'Apple'],
            ['id' => 'MODEL', 'value_name' => 'A2848'],
            ['id' => 'COLOR', 'value_name' => 'Titanio Natural'],
            ['id' => 'YEAR', 'value_name' => '2024'],
            ['id' => 'ITEM_CONDITION', 'value_name' => 'new'],
            ['id' => 'WITH_ORIGINAL_BOX', 'value_name' => 'Yes'],
        ],
        'tags' => ['good_quality_thumbnail', 'brand_verified', 'cart_eligible'],
    ],
    last_synced_at: "2026-02-08 12:30:00",
    external_id: "MLM1234567890",
    external_url: "https://articulo.mercadolibre.com.mx/MLM-1234567890"
}
```

---

## 📊 Resumen de características del seeder

### Personalización por canal:

| Campo | Google | Meta | MercadoLibre |
|-------|--------|------|--------------|
| **custom_title** | 20% tiene custom | 15% tiene custom | 100% tiene custom (+ etiquetas) |
| **custom_price** | 30% custom (±10%) | 25% custom (±5%) | 100% custom (+15-25%) |
| **published_at** | 85% publicados | 80% publicados | 75% publicados |

### Metadata específica:

#### Google Shopping
- GTIN (código de barras EAN13)
- MPN (número de parte del fabricante)
- Categoría de Google
- Peso de envío
- Múltiples imágenes

#### Meta/Facebook
- Custom labels (etiquetas personalizadas)
- Sale price (precio de oferta 30%)
- FB product category
- Vínculos directos al producto

#### MercadoLibre
- Listing type (tipo de publicación: gold, silver, etc.)
- Descripción extendida con formato
- Warranty (garantía)
- Shipping options (envío gratis, pickup)
- Attributes (atributos estructurados)

---

## 🚀 Cómo ejecutar

```bash
# Ejecutar todo el seed (incluye ProductChannelSeeder)
php artisan migrate:fresh --seed

# O ejecutar solo el seeder de canales
php artisan db:seed --class=ProductChannelSeeder
```

---

## 🔍 Consultas de ejemplo después del seed

```php
// Ver todos los canales de un producto
$product = Product::with('channels')->first();
foreach ($product->channels as $channel) {
    echo $channel->name . " - " . $channel->pivot->external_id . "\n";
}

// Productos publicados en Google
$google = Channel::findByCode('google');
$published = $google->products()
    ->wherePivotNotNull('published_at')
    ->count();
echo "Productos en Google: $published\n";

// Productos con precio personalizado en MercadoLibre
$withCustomPrice = ProductChannel::whereHas('channel', function($q) {
    $q->where('code', 'meli');
})->whereNotNull('custom_price')->count();
echo "Productos con precio custom en Meli: $withCustomPrice\n";

// Productos que necesitan sincronización
$needsSync = ProductChannel::where('last_synced_at', '<', now()->subDays(7))
    ->orWhereNull('last_synced_at')
    ->where('is_active', true)
    ->count();
echo "Productos que necesitan sync: $needsSync\n";
```

---

## 📝 Notas importantes

1. **Datos realistas**: El seeder genera datos lo más cercanos a la realidad de cada canal
2. **Metadata diferente**: Cada canal tiene sus propios campos requeridos
3. **Precios variables**: MercadoLibre tiene precios más altos (comisiones), Facebook puede tener ofertas
4. **Publicación gradual**: No todos los productos están publicados inmediatamente
5. **Sincronización**: Algunos productos tienen last_synced_at para simular que ya se enviaron a la API externa

---

## 🎨 Personalización

Puedes modificar el seeder para:
- Cambiar % de publicación por canal
- Ajustar rangos de precios personalizados
- Añadir más atributos en metadata
- Cambiar categorías según tu catálogo
- Ajustar descripciones y títulos de MercadoLibre
