<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Channel>
 */
class ChannelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);
        
        return [
            'name' => ucwords($name),
            'code' => strtolower(str_replace(' ', '_', $name)),
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'description' => fake()->sentence(),
            'is_active' => fake()->boolean(80),
            'config' => [
                'api_url' => fake()->url(),
                'required_fields' => fake()->randomElements(['sku', 'price', 'stock', 'images'], 2),
                'sync_interval' => fake()->randomElement([15, 30, 60, 120]),
            ],
        ];
    }

    /**
     * Indicate that the channel is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the channel is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
