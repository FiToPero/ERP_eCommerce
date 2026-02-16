<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Storage>
 */
class StorageFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company() . ' Storage';
        $code = Str::upper(Str::slug($name, '_'));

        return [
            'name' => $name,
            'code' => $code,
            'description' => fake()->optional()->sentence(),
            'location' => fake()->optional()->address(),
            'is_active' => fake()->boolean(90),
            'metadata' => fake()->optional()->randomElement([
                ['contact' => fake()->phoneNumber()],
                ['notes' => fake()->sentence()],
            ]),
        ];
    }
}
