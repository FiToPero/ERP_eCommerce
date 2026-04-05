<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductMeta>
 */
class ProductMetaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $salePrice = $this->faker->optional(0.4)->randomFloat(2, 5, 500);
        $salePriceStart = $salePrice ? $this->faker->dateTimeBetween('-1 month', 'now') : null;
        $salePriceEnd   = $salePrice ? $this->faker->dateTimeBetween('now', '+2 months') : null;

        return [
            'product_id' => Product::factory(),

            // Estado sincronización
            'is_active'      => $this->faker->boolean(60),
            'status'         => $this->faker->randomElement(['draft', 'pending', 'active', 'rejected']),
            'meta_id'        => $this->faker->optional(0.7)->bothify('meta_####??####'),
            'permalink'      => $this->faker->optional(0.7)->url(),
            'last_synced_at' => $this->faker->optional(0.6)->dateTimeBetween('-1 month', 'now'),
            'sync_errors'    => null,

            // Required
            'title'        => $this->faker->words(4, true),
            'description'  => $this->faker->paragraph(),
            'availability' => $this->faker->randomElement(['in stock', 'out of stock', 'preorder']),
            'condition'    => $this->faker->randomElement(['new', 'used', 'refurbished']),
            'price'        => $this->faker->randomFloat(2, 10, 1000) . ' ARS',
            'link'         => $this->faker->url(),

            // Recommended
            'item_group_id'           => $this->faker->optional(0.5)->bothify('group-####'),
            'color'                   => $this->faker->optional(0.6)->safeColorName(),
            'size'                    => $this->faker->optional(0.5)->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
            'google_product_category' => $this->faker->optional(0.4)->numerify('##########'),
            'product_type'            => $this->faker->optional(0.5)->words(3, true),
            'sale_price'              => $salePrice,
            'sale_price_start'        => $salePriceStart,
            'sale_price_end'          => $salePriceEnd,
        ];
    }

    public function active(): static
    {
        return $this->state(fn() => [
            'is_active' => true,
            'status'    => 'active',
            'meta_id'   => $this->faker->bothify('meta_####??####'),
            'permalink' => $this->faker->url(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn() => [
            'is_active' => false,
            'status'    => 'draft',
            'meta_id'   => null,
            'permalink' => null,
        ]);
    }
}
