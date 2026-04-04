<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductDetail>
 */
class ProductDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dimensionUnit = $this->faker->randomElement(['cm', 'in']);
        $weightUnit    = $this->faker->randomElement(['kg', 'lb', 'g', 'oz']);

        return [
            'product_id' => Product::inRandomOrder()->value('id') ?? Product::factory(),

            // ─── Variantes ────────────────────────────────────────────────────
            'color'       => $this->faker->optional(0.7)->safeColorName(),
            'size'        => $this->faker->optional(0.6)->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL', '36', '38', '40', '42']),
            'size_type'   => $this->faker->optional(0.4)->randomElement(['regular', 'petite', 'maternity', 'big', 'tall', 'plus']),
            'size_system' => $this->faker->optional(0.4)->randomElement(['US', 'UK', 'EU', 'DE', 'FR', 'JP', 'CN', 'IT', 'BR', 'MEX', 'AU']),
            'material'    => $this->faker->optional(0.6)->randomElement(['Cotton', 'Polyester', 'Wool', 'Leather', 'Silk', 'Nylon', 'Linen']),
            'pattern'     => $this->faker->optional(0.4)->randomElement(['solid', 'striped', 'plaid', 'floral', 'geometric', 'animal print']),
            'gender'      => $this->faker->optional(0.5)->randomElement(['male', 'female', 'unisex']),
            'age_group'   => $this->faker->optional(0.5)->randomElement(['newborn', 'infant', 'toddler', 'kids', 'adult']),

            // ─── Dimensiones físicas ──────────────────────────────────────────
            'product_length'         => $this->faker->optional(0.8)->randomFloat(2, 1, 200),
            'product_width'          => $this->faker->optional(0.8)->randomFloat(2, 1, 200),
            'product_height'         => $this->faker->optional(0.8)->randomFloat(2, 1, 200),
            'product_weight'         => $this->faker->optional(0.8)->randomFloat(2, 0.1, 50),
            'product_dimension_unit' => $dimensionUnit,
            'product_weight_unit'    => $weightUnit,

            // ─── Especificaciones / Highlights ───────────────────────────────
            'product_details' => $this->faker->optional(0.7)->passthrough(
                collect(range(1, $this->faker->numberBetween(2, 6)))->map(fn () => [
                    'section'          => $this->faker->randomElement(['Technical', 'Physical', 'Material', 'Warranty']),
                    'attribute_name'   => $this->faker->word(),
                    'attribute_value'  => $this->faker->words(2, true),
                ])->values()->all()
            ),
            'product_highlights' => $this->faker->optional(0.6)->passthrough(
                collect(range(1, $this->faker->numberBetween(2, 5)))->map(fn () =>
                    ucfirst($this->faker->sentence(6))
                )->values()->all()
            ),

            // ─── Custom labels ────────────────────────────────────────────────
            'custom_label_0' => $this->faker->optional(0.4)->word(),
            'custom_label_1' => $this->faker->optional(0.3)->word(),
            'custom_label_2' => $this->faker->optional(0.3)->word(),
            'custom_label_3' => $this->faker->optional(0.2)->word(),
            'custom_label_4' => $this->faker->optional(0.2)->word(),

            // ─── Flags ────────────────────────────────────────────────────────
            'is_adult'  => $this->faker->boolean(5),
            'is_bundle' => $this->faker->boolean(15),
        ];
    }
}
