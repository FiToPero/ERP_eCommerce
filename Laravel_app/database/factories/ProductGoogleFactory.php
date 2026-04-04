<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductGoogle>
 */
class ProductGoogleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status      = $this->faker->randomElement(['draft', 'pending', 'active', 'disapproved']);
        $isActive    = $this->faker->boolean(60);
        $hasSale     = $this->faker->boolean(30);
        $saleStart   = $hasSale ? $this->faker->dateTimeBetween('now', '+7 days') : null;
        $saleEnd     = $hasSale ? $this->faker->dateTimeBetween('+8 days', '+30 days') : null;

        return [
            'product_id' => Product::inRandomOrder()->value('id') ?? Product::factory(),

            // ─── Estado de sincronización ─────────────────────────────────────
            'is_active'      => $isActive,
            'status'         => $status,
            'google_id'      => $status === 'active' ? $this->faker->numerify('online:en:US:##########') : null,
            'google_url'     => $status === 'active' ? $this->faker->url() : null,
            'last_synced_at' => in_array($status, ['active', 'disapproved'])
                ? $this->faker->dateTimeBetween('-30 days', 'now')
                : null,
            'sync_errors' => $status === 'disapproved'
                ? [['code' => 'missing_required_attribute', 'field' => 'gtin', 'message' => 'GTIN is required for this product category.']]
                : null,

            // ─── Required ─────────────────────────────────────────────────────
            'title'        => $this->faker->words(5, true),
            'description'  => $this->faker->paragraph(3),
            'link'         => $this->faker->url(),
            'image_link'   => 'https://via.placeholder.com/800x800.jpg?text=' . urlencode($this->faker->word()),
            'price'        => $this->faker->randomFloat(2, 5, 999),
            'currency'     => $this->faker->randomElement(['USD', 'EUR', 'MXN', 'COP']),
            'availability' => $this->faker->randomElement(['in_stock', 'out_of_stock', 'preorder', 'backorder']),

            // ─── Recommended ──────────────────────────────────────────────────
            'google_product_category' => $this->faker->optional(0.7)->randomElement([
                'Apparel & Accessories > Clothing',
                'Electronics > Computers',
                'Home & Garden > Furniture',
                'Sporting Goods > Exercise & Fitness',
                'Toys & Games',
            ]),
            'product_type'           => $this->faker->optional(0.6)->words(3, true),
            'additional_image_links' => $this->faker->optional(0.5)->passthrough(
                collect(range(1, $this->faker->numberBetween(1, 4)))->map(fn () =>
                    'https://via.placeholder.com/800x800.jpg?text=' . urlencode($this->faker->word())
                )->all()
            ),
            'sale_price'       => $hasSale ? $this->faker->randomFloat(2, 1, 500) : null,
            'sale_price_start' => $saleStart,
            'sale_price_end'   => $saleEnd,
            'item_group_id'    => $this->faker->optional(0.4)->bothify('GRP-????-####'),
        ];
    }

    /**
     * State: product active and synced with Google.
     */
    public function active(): static
    {
        return $this->state(fn () => [
            'is_active'      => true,
            'status'         => 'active',
            'google_id'      => $this->faker->numerify('online:en:US:##########'),
            'google_url'     => $this->faker->url(),
            'last_synced_at' => now(),
            'sync_errors'    => null,
        ]);
    }

    /**
     * State: product disapproved by Google.
     */
    public function disapproved(): static
    {
        return $this->state(fn () => [
            'is_active'      => true,
            'status'         => 'disapproved',
            'google_id'      => null,
            'last_synced_at' => $this->faker->dateTimeBetween('-10 days', 'now'),
            'sync_errors'    => [
                ['code' => 'missing_required_attribute', 'field' => 'gtin', 'message' => 'GTIN is required for this product category.'],
            ],
        ]);
    }
}
