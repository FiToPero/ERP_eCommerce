<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductWeb>
 */
class ProductWebFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $saleStart = $this->faker->optional()->dateTimeBetween('now', '+1 month');
        $saleEnd   = $saleStart
            ? $this->faker->dateTimeBetween($saleStart, '+3 months')
            : null;

        return [
            'product_id'             => Product::factory(),
            'is_active'              => false,
            'status'                 => $this->faker->randomElement(['draft', 'pending', 'active', 'disapproved']),
            'link'                   => $this->faker->optional()->url(),
            'product_type'           => $this->faker->optional()->words(3, true),
            'sale_price'             => $this->faker->optional()->randomFloat(2, 1, 9999),
            'sale_price_start'       => $saleStart,
            'sale_price_end'         => $saleEnd,
            'item_group_id'          => $this->faker->optional()->bothify('GRP-####'),
        ];
    }

    /**
     * Estado: producto activo y publicado.
     */
    public function active(): static
    {
        return $this->state([
            'is_active' => true,
            'status'    => 'active',
        ]);
    }

    /**
     * Estado: borrador (sin datos web aún).
     */
    public function draft(): static
    {
        return $this->state([
            'is_active' => false,
            'status'    => 'draft',
        ]);
    }

    /**
     * Estado: con oferta de precio activa.
     */
    public function withSalePrice(): static
    {
        return $this->state(function () {
            $start = now();
            $end   = now()->addDays($this->faker->numberBetween(3, 30));

            return [
                'sale_price'       => $this->faker->randomFloat(2, 1, 999),
                'sale_price_start' => $start,
                'sale_price_end'   => $end,
            ];
        });
    }
}
