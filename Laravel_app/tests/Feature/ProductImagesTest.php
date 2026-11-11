<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductImagesTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        config(['filesystems.product_images_disk' => 'public']);
        Storage::fake('public');
    }

    public function test_api_returns_ordered_generated_urls_and_no_images_for_seeded_products(): void
    {
        $product = Product::factory()->create();
        $product->images()->create(['path' => 'products/second.jpg', 'position' => 2]);
        $product->images()->create(['path' => 'products/first.jpg', 'position' => 1, 'alt_text' => 'Frente']);
        $empty = Product::factory()->create();

        $response = $this->getJson('/api/products/index')->assertOk();
        $products = collect($response->json())->keyBy('id');

        $this->assertSame(Storage::disk('public')->url('products/first.jpg'), $products[$product->id]['image_link']);
        $this->assertSame([Storage::disk('public')->url('products/second.jpg')], $products[$product->id]['additional_image_links']);
        $this->assertNull($products[$empty->id]['image_link']);
        $this->assertSame([], $products[$empty->id]['additional_image_links']);
    }

    public function test_replacing_and_removing_images_cleans_files_after_commit(): void
    {
        $product = Product::factory()->create();
        Storage::disk('public')->put('products/old.jpg', 'old');
        Storage::disk('public')->put('products/new.jpg', 'new');
        $image = $product->images()->create(['path' => 'products/old.jpg', 'position' => 1]);

        DB::transaction(function () use ($image): void {
            $image->update(['path' => 'products/new.jpg']);
            Storage::disk('public')->assertExists('products/old.jpg');
        });

        Storage::disk('public')->assertMissing('products/old.jpg');
        $image->delete();
        Storage::disk('public')->assertMissing('products/new.jpg');
    }

    public function test_rollback_preserves_old_file_and_soft_delete_preserves_images_until_force_delete(): void
    {
        $product = Product::factory()->create();
        Storage::disk('public')->put('products/keep.jpg', 'image');
        $image = $product->images()->create(['path' => 'products/keep.jpg', 'position' => 1]);

        DB::beginTransaction();
        $image->delete();
        DB::rollBack();
        Storage::disk('public')->assertExists('products/keep.jpg');
        $this->assertDatabaseHas('product_images', ['id' => $image->id]);

        $product->delete();
        Storage::disk('public')->assertExists('products/keep.jpg');
        $product->restore();
        $this->assertCount(1, $product->fresh()->images);
        $product->forceDelete();
        Storage::disk('public')->assertMissing('products/keep.jpg');
        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
    }

    public function test_migration_removes_legacy_image_data(): void
    {
        $migration = require database_path('migrations/2026_11_11_000000_create_product_images_table.php');
        $migration->down();

        $product = Product::factory()->create();
        DB::table('products')->where('id', $product->id)->update([
            'image_link' => 'https://example.test/old.jpg',
            'additional_image_links' => json_encode(['https://example.test/other.jpg']),
        ]);
        DB::table('product_details')->insert([
            'product_id' => $product->id,
            'product_details' => json_encode([
                ['section' => 'Media', 'attribute_name' => 'Main image', 'attribute_value' => 'https://example.test/old.jpg'],
                ['section' => 'General', 'attribute_name' => 'Brand', 'attribute_value' => 'Brand'],
            ]),
        ]);

        $migration->up();

        $this->assertFalse(Schema::hasColumn('products', 'image_link'));
        $this->assertFalse(Schema::hasColumn('products', 'additional_image_links'));
        $details = json_decode(DB::table('product_details')->where('product_id', $product->id)->value('product_details'), true);
        $this->assertSame([['section' => 'General', 'attribute_name' => 'Brand', 'attribute_value' => 'Brand']], $details);
        $this->assertDatabaseCount('product_images', 0);
    }

    public function test_force_deleting_category_cleans_images_in_trashed_descendants(): void
    {
        $category = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $category->id]);
        $product = Product::factory()->create(['category_id' => $child->id]);
        Storage::disk('public')->put('products/child.jpg', 'image');
        $product->images()->create(['path' => 'products/child.jpg', 'position' => 1]);
        $product->delete();
        $child->delete();

        $category->forceDelete();

        Storage::disk('public')->assertMissing('products/child.jpg');
        $this->assertDatabaseCount('product_images', 0);
    }

    public function test_filament_can_upload_a_product_image(): void
    {
        $this->assertTrue(app()->runningUnitTests(), app()->environment());
        Gate::before(fn () => true);
        $this->actingAs(User::factory()->create());
        $category = Category::factory()->create();
        $file = UploadedFile::fake()->image('product.jpg');

        $component = Livewire::test(CreateProduct::class)
            ->fillForm([
                'category_id' => $category->id,
                'name' => 'Producto',
                'slug' => 'producto',
                'sku' => 'PROD-1',
                'short_description' => 'Descripción',
                'condition' => 'new',
                'images' => [['path' => [$file], 'alt_text' => 'Frente']],
            ])
            ->assertFormSet(['name' => 'Producto', 'category_id' => $category->id])
            ->call('create');
        $this->assertSame([], $component->instance()->getErrorBag()->messages());

        $image = ProductImage::firstOrFail();
        $this->assertSame('Frente', $image->alt_text);
        $this->assertSame(1, $image->position);
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_filament_reorders_and_removes_images(): void
    {
        Gate::before(fn () => true);
        $this->actingAs(User::factory()->create());
        $product = Product::factory()->create();
        Storage::disk('public')->put('products/first.jpg', 'first');
        Storage::disk('public')->put('products/second.jpg', 'second');
        $first = $product->images()->create(['path' => 'products/first.jpg', 'position' => 1]);
        $second = $product->images()->create(['path' => 'products/second.jpg', 'position' => 2]);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->set('data.images', [
                "record-{$second->id}" => ['path' => ['products/second.jpg']],
                "record-{$first->id}" => ['path' => ['products/first.jpg']],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('products/second.jpg', $product->fresh()->images->first()->path);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->set('data.images', ["record-{$second->id}" => ['path' => ['products/second.jpg']]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseMissing('product_images', ['id' => $first->id]);
        Storage::disk('public')->assertMissing('products/first.jpg');
        Storage::disk('public')->assertExists('products/second.jpg');
    }

    public function test_filament_replaces_an_image(): void
    {
        Gate::before(fn () => true);
        $this->actingAs(User::factory()->create());
        $product = Product::factory()->create();
        Storage::disk('public')->put('products/old.jpg', 'old');
        $image = $product->images()->create(['path' => 'products/old.jpg', 'position' => 1]);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->set("data.images.record-{$image->id}.path", [UploadedFile::fake()->image('new.png')])
            ->call('save')
            ->assertHasNoFormErrors();

        $newPath = $image->fresh()->path;
        $this->assertNotSame('products/old.jpg', $newPath);
        Storage::disk('public')->assertMissing('products/old.jpg');
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_filament_rejects_non_images_and_oversized_images(): void
    {
        Gate::before(fn () => true);
        $this->actingAs(User::factory()->create());
        $category = Category::factory()->create();

        foreach ([
            UploadedFile::fake()->create('file.txt', 10, 'text/plain'),
            UploadedFile::fake()->image('large.jpg')->size(5121),
        ] as $file) {
            Livewire::test(CreateProduct::class)
                ->fillForm([
                    'category_id' => $category->id,
                    'name' => 'Producto',
                    'slug' => 'producto',
                    'sku' => 'PROD-1',
                    'short_description' => 'Descripción',
                    'condition' => 'new',
                    'images' => [['path' => [$file]]],
                ])
                ->call('create')
                ->assertHasFormErrors(['images.0.path']);
        }

        $this->assertDatabaseCount('product_images', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('products'));
    }

    public function test_filament_rejects_more_than_ten_images(): void
    {
        Gate::before(fn () => true);
        $this->actingAs(User::factory()->create());
        $category = Category::factory()->create();

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'category_id' => $category->id,
                'name' => 'Producto',
                'slug' => 'producto',
                'sku' => 'PROD-1',
                'short_description' => 'Descripción',
                'condition' => 'new',
            ])
            ->set('data.images', array_fill(0, 11, ['path' => []]))
            ->call('create')
            ->assertHasFormErrors(['images' => 'max']);

        $this->assertDatabaseCount('product_images', 0);
    }

    public function test_filament_rejects_paths_that_are_not_the_record_image(): void
    {
        Gate::before(fn () => true);
        $this->actingAs(User::factory()->create());
        $product = Product::factory()->create();
        Storage::disk('public')->put('products/first.jpg', 'first');
        Storage::disk('public')->put('other/private.txt', 'private');
        $image = $product->images()->create(['path' => 'products/first.jpg', 'position' => 1]);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->set('data.images', ["record-{$image->id}" => ['path' => ['other/private.txt']]])
            ->call('save')
            ->assertHasFormErrors(["images.record-{$image->id}.path"]);

        $this->assertDatabaseHas('product_images', ['id' => $image->id, 'path' => 'products/first.jpg']);
        Storage::disk('public')->assertExists('other/private.txt');
    }
}
