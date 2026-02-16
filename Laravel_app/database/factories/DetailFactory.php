<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Detail>
 */
class DetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $weightUnits = ['kg', 'lb', 'g', 'oz'];
        $dimensionUnits = ['cm', 'in', 'm'];
        $colors = ['Black', 'White', 'Silver', 'Gold', 'Blue', 'Red', 'Green', 'Gray', 'Rose Gold', 'Purple'];
        $materials = ['Plastic', 'Metal', 'Aluminum', 'Stainless Steel', 'Glass', 'Wood', 'Leather', 'Fabric', 'Carbon Fiber'];
        $brands = ['Apple', 'Samsung', 'Sony', 'LG', 'Dell', 'HP', 'Lenovo', 'Asus', 'Microsoft', 'Google'];
        $countries = ['China', 'USA', 'Japan', 'South Korea', 'Germany', 'Taiwan', 'Vietnam', 'Mexico', 'Thailand'];

        return [
            'product_id' => Product::factory(),
            'model' => strtoupper(fake()->bothify('??-####')),
            
            // Physical attributes
            'weight' => fake()->randomFloat(2, 0.1, 50),
            'weight_unit' => fake()->randomElement($weightUnits),
            'height' => fake()->randomFloat(2, 1, 100),
            'width' => fake()->randomFloat(2, 1, 100),
            'length' => fake()->randomFloat(2, 1, 100),
            'dimensions_unit' => fake()->randomElement($dimensionUnits),
            
            // Appearance
            'color' => fake()->randomElement($colors),
            'material' => fake()->randomElement($materials),
            
            // Manufacturing
            'brand' => fake()->randomElement($brands),
            'manufacturer' => fake()->company(),
            'origin' => fake()->randomElement($countries),
            'year' => fake()->numberBetween(2018, 2026),
            
            // Warranty
            'warranty_period' => fake()->randomElement([6, 12, 24, 36, 48]),
            'warranty_details' => fake()->optional(0.7)->sentence(10),
        ];
    }
}
