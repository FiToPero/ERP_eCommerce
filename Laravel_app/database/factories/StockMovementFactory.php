<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockMovement>
 */
class StockMovementFactory extends Factory
{
    public function definition(): array
    {
        $direction = fake()->randomElement(['in', 'out']);
        $type = $direction === 'in'
            ? fake()->randomElement(['purchase', 'return', 'adjust', 'transfer_in'])
            : fake()->randomElement(['sale', 'damage', 'adjust', 'transfer_out']);

        return [
            'product_id' => Product::factory(),
            'storage_id' => Storage::factory(),
            'direction' => $direction,
            'type' => $type,
            'quantity' => fake()->randomFloat(2, 1, 200),
            'unit_cost' => $direction === 'in' ? fake()->optional()->randomFloat(2, 1, 500) : null,
            'reference_type' => fake()->optional()->randomElement(['purchase_order', 'sale_order', 'transfer', 'adjustment']),
            'reference_id' => fake()->optional()->uuid(),
            'moved_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'note' => fake()->optional()->sentence(),
            'metadata' => fake()->optional()->randomElement([
                ['source' => 'import'],
                ['source' => 'manual'],
            ]),
        ];
    }
}
