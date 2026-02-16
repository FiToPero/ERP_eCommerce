<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'company_name' => $this->faker->company(),
            'cuit' => $this->faker->unique()->numerify('##-########-#'),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'zip_code' => $this->faker->postcode(),
            'birthdate' => $this->faker->date(),
            'bio' => $this->faker->text(),
            'website' => $this->faker->url(),
            'instagram' => $this->faker->userName(),
            'twitter' => $this->faker->userName(),
            'linkedin' => $this->faker->userName(),
            'github' => $this->faker->userName(),
            'facebook' => $this->faker->userName(),
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ];
    }
}
