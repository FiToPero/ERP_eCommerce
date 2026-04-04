<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductMercadolibre>
 */
class ProductMercadolibreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id'         => Product::factory(),

            // Sincronización
            'is_active'          => false,
            'status'             => $this->faker->randomElement(['draft', 'pending', 'active', 'paused', 'error']),
            'ml_id'              => $this->faker->optional()->bothify('MLA#########'),
            'ml_url'             => $this->faker->optional()->url(),
            'last_synced_at'     => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'sync_errors'        => null,

            // Required
            'title'              => $this->faker->optional()->sentence(4),
            'category_id'        => $this->faker->optional()->bothify('MLA####'),
            'price'              => $this->faker->optional()->randomFloat(2, 100, 999999),
            'currency_id'        => 'ARS',
            'available_quantity' => $this->faker->optional()->numberBetween(0, 500),
            'buying_mode'        => 'buy_it_now',
            'listing_type_id'    => $this->faker->optional()->randomElement(['gold_special', 'gold_pro', 'free']),
            'condition'          => $this->faker->randomElement(['new', 'used']),
            'pictures'           => $this->faker->optional()->randomElements(
                array_map(fn () => ['source' => $this->faker->imageUrl(800, 800, 'products')], range(1, 5)),
                $this->faker->numberBetween(1, 4)
            ),

            // Conditional
            'brand'              => $this->faker->optional()->company(),
            'gtin'               => $this->faker->optional()->ean13(),
            'attributes'         => null,

            // Recommended
            'sale_terms'         => null,
            'shipping'           => null,
            'video_id'           => $this->faker->optional()->bothify('??????????'),
            'warranty'           => $this->faker->optional()->randomElement(['12 meses', '6 meses', 'Sin garantía']),
            'variations'         => null,
        ];
    }

    /**
     * Publicación activa y sincronizada.
     */
    public function active(): static
    {
        return $this->state([
            'is_active'      => true,
            'status'         => 'active',
            'ml_id'          => $this->faker->bothify('MLA#########'),
            'ml_url'         => $this->faker->url(),
            'last_synced_at' => now(),
        ]);
    }

    /**
     * Borrador sin publicar aún.
     */
    public function draft(): static
    {
        return $this->state([
            'is_active' => false,
            'status'    => 'draft',
            'ml_id'     => null,
            'ml_url'    => null,
        ]);
    }

    /**
     * Con errores de sincronización.
     */
    public function withSyncErrors(): static
    {
        return $this->state([
            'status'      => 'error',
            'sync_errors' => [
                ['code' => $this->faker->bothify('item.###'), 'message' => $this->faker->sentence()],
            ],
        ]);
    }

    /**
     * Con variantes de producto.
     */
    public function withVariations(): static
    {
        return $this->state([
            'variations' => array_map(fn () => [
                'attribute_combinations' => [['id' => 'COLOR', 'value_name' => $this->faker->colorName()]],
                'price'                  => $this->faker->randomFloat(2, 100, 99999),
                'available_quantity'     => $this->faker->numberBetween(1, 100),
                'picture_ids'            => [],
            ], range(1, $this->faker->numberBetween(2, 4))),
        ]);
    }
}
