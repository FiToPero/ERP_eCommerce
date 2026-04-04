<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name      = $this->faker->unique()->words(3, true);
        $slug      = Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1000, 9999);
        $brand     = $this->faker->optional(0.8)->company();
        $barcode   = $this->faker->optional(0.7)->ean13();
        $mpn       = $this->faker->optional(0.6)->bothify('MPN-##??-####');
        $condition = $this->faker->randomElement(['new', 'refurbished', 'used']);

        return [
            'category_id'        => Category::inRandomOrder()->value('id') ?? Category::factory(),
            'name'               => ucwords($name),
            'sku'                => strtoupper($this->faker->unique()->bothify('SKU-??-####')),
            'slug'               => $slug,
            'short_description'  => $this->faker->sentence(10),
            'description'        => $this->faker->optional(0.9)->paragraphs(2, true),
            'brand'              => $brand,
            'barcode'            => $barcode,
            'mpn'                => $mpn,
            'identifier_exists'  => $brand || $barcode || $mpn ? true : false,
            'condition'          => $condition,
            'availability_date'  => $condition === 'new'
                ? null
                : $this->faker->optional(0.3)->dateTimeBetween('now', '+6 months'),
            'web_requirements'    => null,
            'google_requirements' => null,
            'ml_requirements'     => null,
            'meta_requirements'   => null,
        ];
    }
}
