<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Channel;
use App\Models\ProductChannel;
use Illuminate\Database\Seeder;

class ProductChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener los canales predefinidos
        $google = Channel::where('code', 'google')->first();
        $meta = Channel::where('code', 'meta')->first();
        $meli = Channel::where('code', 'meli')->first();

        if (!$google || !$meta || !$meli) {
            $this->command->error('⚠️  Los canales no existen. Ejecuta primero la migración de channels.');
            return;
        }

        // Obtener productos existentes
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->warn('⚠️  No hay productos. Crea productos primero con ProductSeeder.');
            return;
        }

        $this->command->info("📦 Publicando {$products->count()} productos en 3 canales...");

        foreach ($products as $index => $product) {
            // Publicar en Google Shopping
            $this->publishToGoogle($product, $google);
            
            // Publicar en Meta (80% de los productos)
            if ($index % 5 !== 0) {
                $this->publishToMeta($product, $meta);
            }
            
            // Publicar en MercadoLibre (70% de los productos)
            if ($index % 3 !== 0) {
                $this->publishToMeli($product, $meli);
            }
        }

        $this->command->info('✅ Productos publicados en canales de venta exitosamente.');
    }

    /**
     * Publish product to Google Shopping.
     */
    protected function publishToGoogle(Product $product, Channel $channel): void
    {
        $isPublished = fake()->boolean(85); // 85% publicados

        ProductChannel::create([
            'product_id' => $product->id,
            'channel_id' => $channel->id,
            'is_active' => true,
            'published_at' => $isPublished ? fake()->dateTimeBetween('-3 months', 'now') : null,
            'custom_title' => fake()->optional(0.2)->sentence(5), // 20% tiene título custom
            'custom_price' => fake()->optional(0.3)->randomFloat(2, $product->price * 0.9, $product->price * 1.1),
            'metadata' => [
                'gtin' => fake()->ean13(),
                'mpn' => strtoupper(fake()->bothify('MPN-####??')),
                'brand' => $product->detail?->brand ?? fake()->company(),
                'condition' => 'new',
                'availability' => $product->stock > 0 ? 'in stock' : 'out of stock',
                'price' => $product->price . ' USD',
                'shipping_weight' => $product->detail?->weight ?? fake()->randomFloat(2, 0.1, 10),
                'shipping_weight_unit' => $product->detail?->weight_unit ?? 'kg',
                'google_product_category' => $this->getGoogleCategory($product),
                'product_type' => $product->category?->name,
                'image_link' => $product->images->first()?->url,
                'additional_image_link' => $product->images->skip(1)->take(5)->pluck('url')->toArray(),
            ],
            'last_synced_at' => $isPublished ? fake()->dateTimeBetween('-7 days', 'now') : null,
            'external_id' => $isPublished ? 'GS-' . strtoupper(fake()->bothify('??###??###')) : null,
            'external_url' => $isPublished ? 'https://merchants.google.com/product/' . fake()->uuid() : null,
        ]);
    }

    /**
     * Publish product to Meta/Facebook.
     */
    protected function publishToMeta(Product $product, Channel $channel): void
    {
        $isPublished = fake()->boolean(80);
        $saleEffectiveDate = fake()->optional(0.3)->dateTimeBetween('now', '+30 days');

        ProductChannel::create([
            'product_id' => $product->id,
            'channel_id' => $channel->id,
            'is_active' => true,
            'published_at' => $isPublished ? fake()->dateTimeBetween('-2 months', 'now') : null,
            'custom_title' => fake()->optional(0.15)->sentence(6),
            'custom_price' => fake()->optional(0.25)->randomFloat(2, $product->price * 0.95, $product->price * 1.05),
            'metadata' => [
                'availability' => $product->stock > 0 ? 'in stock' : 'out of stock',
                'condition' => 'new',
                'price' => $product->price . ' USD',
                'link' => rtrim(config('app.url'), '/') . '/products/' . ($product->slug ?? $product->id),
                'image_link' => $product->images->first()?->url,
                'brand' => $product->detail?->brand ?? fake()->company(),
                'fb_product_category' => $this->getFacebookCategory($product),
                'product_type' => $product->category?->name,
                'sale_price' => fake()->optional(0.3)->randomFloat(2, $product->price * 0.8, $product->price * 0.95),
                'sale_price_effective_date' => $saleEffectiveDate?->format('Y-m-d'),
                'custom_label_0' => fake()->optional()->randomElement(['Bestseller', 'New Arrival', 'Featured', 'Sale']),
                'custom_label_1' => $product->category?->name,
                'custom_label_2' => fake()->optional()->randomElement(['Premium', 'Budget', 'Mid-Range']),
            ],
            'last_synced_at' => $isPublished ? fake()->dateTimeBetween('-5 days', 'now') : null,
            'external_id' => $isPublished ? 'FB-' . fake()->numerify('##########') : null,
            'external_url' => $isPublished ? 'https://www.facebook.com/commerce/products/' . fake()->uuid() : null,
        ]);
    }

    /**
     * Publish product to MercadoLibre.
     */
    protected function publishToMeli(Product $product, Channel $channel): void
    {
        $isPublished = fake()->boolean(75);
        $listingTypes = ['gold_special', 'gold_premium', 'gold', 'silver', 'bronze', 'free'];
        $listingType = fake()->randomElement($listingTypes);

        // Precio para MercadoLibre (generalmente más alto por comisiones)
        $meliPrice = $product->price * fake()->randomFloat(2, 1.1, 1.25);

        ProductChannel::create([
            'product_id' => $product->id,
            'channel_id' => $channel->id,
            'is_active' => true,
            'published_at' => $isPublished ? fake()->dateTimeBetween('-2 months', 'now') : null,
            'custom_title' => $product->name . ' - ' . fake()->optional()->randomElement(['Envío Gratis', 'Oferta', 'Original']),
            'custom_description' => $this->generateMeliDescription($product),
            'custom_price' => $meliPrice,
            'metadata' => [
                'listing_type' => $listingType,
                'category_id' => $this->getMeliCategory($product),
                'price' => $meliPrice,
                'currency_id' => 'MXN', // o USD, según tu país
                'available_quantity' => $product->stock,
                'buying_mode' => 'buy_it_now',
                'condition' => 'new',
                'warranty' => $product->detail?->warranty_period 
                    ? $product->detail->warranty_period . ' meses de garantía' 
                    : '12 meses de garantía del vendedor',
                'shipping' => [
                    'mode' => fake()->randomElement(['me2', 'custom']),
                    'free_shipping' => fake()->boolean(40),
                    'local_pick_up' => fake()->boolean(30),
                ],
                'pictures' => $product->images->pluck('url')->toArray(),
                'video_id' => fake()->optional(0.1)->bothify('youtube_?????????'),
                'attributes' => $this->generateMeliAttributes($product),
                'tags' => fake()->randomElements([
                    'good_quality_thumbnail',
                    'brand_verified',
                    'immediate_payment',
                    'cart_eligible',
                ], fake()->numberBetween(0, 3)),
            ],
            'last_synced_at' => $isPublished ? fake()->dateTimeBetween('-10 days', 'now') : null,
            'external_id' => $isPublished ? 'MLM' . fake()->numerify('##########') : null,
            'external_url' => $isPublished ? 'https://articulo.mercadolibre.com.mx/MLM-' . fake()->numerify('##########') : null,
        ]);
    }

    /**
     * Get Google Shopping category.
     */
    protected function getGoogleCategory(Product $product): string
    {
        $categories = [
            'Electrónica' => 'Electronics > Computers & Accessories',
            'Ropa' => 'Apparel & Accessories',
            'Hogar' => 'Home & Garden',
        ];

        $parentCategory = $product->category?->parent?->name ?? $product->category?->name;
        
        return $categories[$parentCategory] ?? 'Shopping > General';
    }

    /**
     * Get Facebook product category.
     */
    protected function getFacebookCategory(Product $product): string
    {
        $categories = [
            'Electrónica' => 'Electronics & Computers',
            'Ropa' => 'Clothing & Accessories',
            'Hogar' => 'Home & Garden',
        ];

        $parentCategory = $product->category?->parent?->name ?? $product->category?->name;
        
        return $categories[$parentCategory] ?? 'Other';
    }

    /**
     * Get MercadoLibre category ID.
     */
    protected function getMeliCategory(Product $product): string
    {
        $categories = [
            'Smartphones' => 'MLM1055',
            'Laptops' => 'MLM1652',
            'Tablets' => 'MLM1051',
            'Ropa' => 'MLM1430',
            'Hogar' => 'MLM1574',
        ];

        $subcategory = $product->category?->name;
        
        return $categories[$subcategory] ?? 'MLM1000'; // Categoría genérica
    }

    /**
     * Generate MercadoLibre description.
     */
    protected function generateMeliDescription(Product $product): string
    {
        $description = "✨ " . strtoupper($product->name) . " ✨\n\n";
        $description .= $product->description . "\n\n";
        
        if ($product->detail) {
            $description .= "📦 ESPECIFICACIONES TÉCNICAS:\n";
            if ($product->detail->brand) $description .= "• Marca: " . $product->detail->brand . "\n";
            if ($product->detail->model) $description .= "• Modelo: " . $product->detail->model . "\n";
            if ($product->detail->color) $description .= "• Color: " . $product->detail->color . "\n";
            if ($product->detail->weight) $description .= "• Peso: " . $product->detail->weight . " " . $product->detail->weight_unit . "\n";
            $description .= "\n";
        }
        
        $description .= "✅ GARANTÍA: " . ($product->detail?->warranty_period ?? 12) . " meses\n";
        $description .= "🚚 ENVÍO: A todo México\n";
        $description .= "💳 PAGOS: Aceptamos todos los métodos de pago\n\n";
        $description .= "¡COMPRA CON CONFIANZA!";
        
        return $description;
    }

    /**
     * Generate MercadoLibre attributes.
     */
    protected function generateMeliAttributes(Product $product): array
    {
        $attributes = [];
        
        if ($product->detail) {
            if ($product->detail->brand) {
                $attributes[] = ['id' => 'BRAND', 'value_name' => $product->detail->brand];
            }
            if ($product->detail->model) {
                $attributes[] = ['id' => 'MODEL', 'value_name' => $product->detail->model];
            }
            if ($product->detail->color) {
                $attributes[] = ['id' => 'COLOR', 'value_name' => $product->detail->color];
            }
            if ($product->detail->year) {
                $attributes[] = ['id' => 'YEAR', 'value_name' => (string)$product->detail->year];
            }
        }
        
        $attributes[] = ['id' => 'ITEM_CONDITION', 'value_name' => 'new'];
        $attributes[] = ['id' => 'WITH_ORIGINAL_BOX', 'value_name' => 'Yes'];
        
        return $attributes;
    }
}
