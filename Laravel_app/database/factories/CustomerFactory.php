<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $web1 = env('FRONTEND_URL_WEB_01');
        $web2 = env('FRONTEND_URL_WEB_02');

        return [
            'customer_name' => $this->faker->name(),
            'type' => $this->faker->randomElement(['personal', 'business']),
            'web' => $this->faker->randomElement([$web1, $web2]),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('12345678'),
            'remember_token' => null,
        ];
    }
}
