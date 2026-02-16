<?php

namespace Database\Factories;

use App\Models\Channel;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductChannel>
 */
class ProductChannelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isActive = fake()->boolean(70);
        $isPublished = $isActive && fake()->boolean(80);
        
        return [
            'product_id' => Product::factory(),
            'channel_id' => Channel::factory(),
            'is_active' => $isActive,
            'published_at' => $isPublished ? fake()->dateTimeBetween('-6 months', 'now') : null,
            'custom_title' => fake()->optional(0.3)->sentence(4),
            'custom_description' => fake()->optional(0.2)->paragraph(),
            'custom_price' => fake()->optional(0.3)->randomFloat(2, 10, 1000),
            'metadata' => $this->generateMetadata(),
            'last_synced_at' => $isPublished ? fake()->dateTimeBetween('-7 days', 'now') : null,
            'external_id' => $isPublished ? fake()->uuid() : null,
            'external_url' => $isPublished ? fake()->url() : null,
        ];
    }

    /**
     * Generate channel-specific metadata.
     */
    protected function generateMetadata(): array
    {
        $channels = ['google', 'meta', 'meli'];
        $channel = fake()->randomElement($channels);
        
        $metadata = [];
        
        switch ($channel) {
            case 'google':
                $metadata = [
                    'gtin' => fake()->ean13(),
                    'mpn' => strtoupper(fake()->bothify('MPN-####??')),
                    'condition' => fake()->randomElement(['new', 'refurbished', 'used']),
                    'availability' => fake()->randomElement(['in stock', 'out of stock', 'preorder']),
                    'shipping_weight' => fake()->randomFloat(2, 0.1, 10),
                    'google_product_category' => fake()->numberBetween(1000, 9999),
                ];
                break;
                
            case 'meta':
                $metadata = [
                    'availability' => fake()->randomElement(['in stock', 'out of stock', 'available for order']),
                    'condition' => fake()->randomElement(['new', 'refurbished', 'used']),
                    'fb_product_category' => fake()->words(3, true),
                    'custom_label_0' => fake()->optional()->word(),
                    'custom_label_1' => fake()->optional()->word(),
                ];
                break;
                
            case 'meli':
                $metadata = [
                    'listing_type' => fake()->randomElement(['gold_special', 'gold_premium', 'gold', 'silver', 'bronze', 'free']),
                    'category_id' => 'MLM' . fake()->numerify('####'),
                    'warranty' => fake()->randomElement(['12 meses', '24 meses', '6 meses', 'Sin garantía']),
                    'shipping_mode' => fake()->randomElement(['me2', 'custom']),
                    'condition' => fake()->randomElement(['new', 'used']),
                ];
                break;
        }
        
        return $metadata;
    }

    /**
     * Indicate that the product channel is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the product channel is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the product channel is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'last_synced_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'external_id' => fake()->uuid(),
            'external_url' => fake()->url(),
        ]);
    }

    /**
     * Generate metadata for Google Shopping.
     */
    public function forGoogle(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => [
                'gtin' => fake()->ean13(),
                'mpn' => strtoupper(fake()->bothify('MPN-####??')),
                'condition' => 'new',
                'availability' => 'in stock',
                'shipping_weight' => fake()->randomFloat(2, 0.1, 10),
                'google_product_category' => fake()->numberBetween(1000, 9999),
            ],
        ]);
    }

    /**
     * Generate metadata for Meta/Facebook.
     */
    public function forMeta(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => [
                'availability' => 'in stock',
                'condition' => 'new',
                'fb_product_category' => fake()->words(3, true),
            ],
        ]);
    }

    /**
     * Generate metadata for MercadoLibre.
     */
    public function forMeli(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => [
                'listing_type' => 'gold_special',
                'category_id' => 'MLM' . fake()->numerify('####'),
                'warranty' => '12 meses',
                'shipping_mode' => 'me2',
                'condition' => 'new',
            ],
        ]);
    }
}
