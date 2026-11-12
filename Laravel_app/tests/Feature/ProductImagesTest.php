<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Database\Seeders\FactoriesSeeders\ProductSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
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
        $product->images()->create(['path' => 'products/second.jpg', 'position' => 2, 'filename_description' => 'dorso']);
        $product->images()->create(['path' => 'products/first.jpg', 'position' => 1, 'filename_description' => 'frente', 'alt_text' => 'Frente']);
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
        $image = $product->images()->create(['path' => 'products/old.jpg', 'position' => 1, 'filename_description' => 'frente']);

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
        $image = $product->images()->create(['path' => 'products/keep.jpg', 'position' => 1, 'filename_description' => 'frente']);

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

    public function test_image_schema_has_no_legacy_columns_or_reservation_table(): void
    {
        $this->assertFalse(Schema::hasColumn('products', 'image_link'));
        $this->assertFalse(Schema::hasColumn('products', 'additional_image_links'));
        $this->assertFalse(Schema::hasTable('product_image_names'));
        $this->assertTrue(Schema::hasColumn('product_images', 'filename_description'));
    }

    public function test_product_seeder_saves_descriptions_and_named_images(): void
    {
        Category::factory()->create(['slug' => 'iphone']);
        $image = UploadedFile::fake()->image('example.jpg');
        Http::fake(['picsum.photos/*' => Http::response(file_get_contents($image->getRealPath()))]);

        $this->seed(ProductSeeder::class);
        $images = ProductImage::with('product')->get();
        $this->assertCount(4, $images);

        foreach ($images as $productImage) {
            $this->assertSame("ejemplo-{$productImage->position}", $productImage->filename_description);
            $this->assertStringEndsWith("-ejemplo-{$productImage->position}.webp", $productImage->path);
            Storage::disk('public')->assertExists($productImage->path);
        }

        $this->seed(ProductSeeder::class);
        $this->assertDatabaseCount('product_images', 4);
    }

    public function test_force_deleting_category_cleans_images_in_trashed_descendants(): void
    {
        $category = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $category->id]);
        $product = Product::factory()->create(['category_id' => $child->id]);
        Storage::disk('public')->put('products/child.jpg', 'image');
        $product->images()->create(['path' => 'products/child.jpg', 'position' => 1, 'filename_description' => 'frente']);
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
                'images' => [['path' => [$file], 'filename_description' => 'Vista frontal', 'alt_text' => 'Frente']],
            ])
            ->assertFormSet(['name' => 'Producto', 'category_id' => $category->id])
            ->call('create');
        $this->assertSame([], $component->instance()->getErrorBag()->messages());

        $image = ProductImage::firstOrFail();
        $this->assertSame('Frente', $image->alt_text);
        $this->assertSame(1, $image->position);
        $this->assertSame('Vista frontal', $image->filename_description);
        $this->assertSame('products/'.$image->product->image_directory.'/producto-vista-frontal.webp', $image->path);
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_multiple_uploads_use_descriptions_without_position_in_filenames(): void
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
                'images' => [
                    ['filename_description' => 'frente', 'path' => [UploadedFile::fake()->image('first.jpg')]],
                    ['filename_description' => 'dorso', 'path' => [UploadedFile::fake()->image('second.jpg')]],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('sku', 'PROD-1')->firstOrFail();
        $this->assertSame(
            ["products/{$product->image_directory}/producto-frente.webp", "products/{$product->image_directory}/producto-dorso.webp"],
            $product->images->pluck('path')->all(),
        );
        $this->assertSame([1, 2], $product->images->pluck('position')->all());
        foreach ($product->images as $image) {
            Storage::disk('public')->assertExists($image->path);
        }
    }

    public function test_filament_reorders_and_removes_images(): void
    {
        Gate::before(fn () => true);
        $this->actingAs(User::factory()->create());
        $product = Product::factory()->create();
        Storage::disk('public')->put('products/first.jpg', 'first');
        Storage::disk('public')->put('products/second.jpg', 'second');
        $first = $product->images()->create(['path' => 'products/first.jpg', 'position' => 1, 'filename_description' => 'frente']);
        $second = $product->images()->create(['path' => 'products/second.jpg', 'position' => 2, 'filename_description' => 'dorso']);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->set('data.images', [
                "record-{$second->id}" => ['path' => ['products/second.jpg'], 'filename_description' => 'dorso'],
                "record-{$first->id}" => ['path' => ['products/first.jpg'], 'filename_description' => 'frente'],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('products/second.jpg', $product->fresh()->images->first()->path);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->set('data.images', ["record-{$second->id}" => ['path' => ['products/second.jpg'], 'filename_description' => 'dorso']])
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
        $image = $product->images()->create(['path' => 'products/old.jpg', 'position' => 1, 'filename_description' => 'frente']);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->set("data.images.record-{$image->id}.path", [UploadedFile::fake()->image('new.png')])
            ->call('save')
            ->assertHasNoFormErrors();

        $newPath = $image->fresh()->path;
        $this->assertNotSame('products/old.jpg', $newPath);
        Storage::disk('public')->assertMissing('products/old.jpg');
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_filament_can_replace_an_image_at_its_existing_path(): void
    {
        Gate::before(fn () => true);
        $this->actingAs(User::factory()->create());
        $product = Product::factory()->create(['name' => 'Producto']);
        $path = "products/{$product->image_directory}/producto-frente.webp";
        Storage::disk('public')->put($path, 'old');
        $image = $product->images()->create(['path' => $path, 'position' => 1, 'filename_description' => 'frente']);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->set("data.images.record-{$image->id}.path", [UploadedFile::fake()->image('new.png')])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($path, $image->fresh()->path);
        $this->assertNotSame('old', Storage::disk('public')->get($path));
    }

    public function test_filament_requires_a_usable_filename_description(): void
    {
        Gate::before(fn () => true);
        $this->actingAs(User::factory()->create());
        $category = Category::factory()->create();

        foreach (['', '!!!'] as $description) {
            Livewire::test(CreateProduct::class)
                ->fillForm([
                    'category_id' => $category->id,
                    'name' => 'Producto',
                    'slug' => 'producto',
                    'sku' => 'PROD-1',
                    'short_description' => 'Descripción',
                    'condition' => 'new',
                    'images' => [['filename_description' => $description, 'path' => [UploadedFile::fake()->image('product.jpg')]]],
                ])
                ->call('create')
                ->assertHasFormErrors(['images.0.filename_description']);
        }

        $this->assertDatabaseCount('product_images', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('products'));
    }

    public function test_filament_rejects_duplicate_filename_descriptions_on_create_and_update(): void
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
                'images' => [
                    ['filename_description' => 'Vista frontal', 'path' => [UploadedFile::fake()->image('a.jpg')]],
                    ['filename_description' => 'vista-frontal', 'path' => [UploadedFile::fake()->image('b.jpg')]],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['images.0.filename_description', 'images.1.filename_description']);

        $this->assertSame([], Storage::disk('public')->allFiles('products'));
        $product = Product::factory()->create(['category_id' => $category->id]);
        Storage::disk('public')->put('products/first.jpg', 'first');
        Storage::disk('public')->put('products/second.jpg', 'second');
        $first = $product->images()->create(['path' => 'products/first.jpg', 'position' => 1, 'filename_description' => 'frente']);
        $second = $product->images()->create(['path' => 'products/second.jpg', 'position' => 2, 'filename_description' => 'dorso']);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->set("data.images.record-{$second->id}.filename_description", 'Frente')
            ->call('save')
            ->assertHasFormErrors(["images.record-{$second->id}.filename_description"]);

        $this->assertSame('dorso', $second->fresh()->filename_description);
        $this->assertSame('frente', $first->fresh()->filename_description);
    }

    public function test_editing_name_and_description_does_not_rename_existing_file(): void
    {
        Gate::before(fn () => true);
        $this->actingAs(User::factory()->create());
        $product = Product::factory()->create(['name' => 'Producto']);
        $path = "products/{$product->image_directory}/producto-frente.webp";
        Storage::disk('public')->put($path, 'image');
        $image = $product->images()->create(['path' => $path, 'position' => 1, 'filename_description' => 'frente']);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->set('data.name', 'Otro nombre')
            ->set("data.images.record-{$image->id}.filename_description", 'dorso')
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($path, $image->fresh()->path);
        $this->assertSame('dorso', $image->fresh()->filename_description);
        Storage::disk('public')->assertExists($path);
    }

    public function test_filament_rejects_conflicting_files_without_overwriting_them(): void
    {
        Gate::before(fn () => true);
        $this->actingAs(User::factory()->create());
        $product = Product::factory()->create(['name' => 'Producto']);
        $path = "products/{$product->image_directory}/producto-frente.webp";
        Storage::disk('public')->put($path, 'orphan');
        Storage::disk('public')->put('products/old.jpg', 'old');
        $image = $product->images()->create(['path' => 'products/old.jpg', 'position' => 1, 'filename_description' => 'frente']);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->set("data.images.record-{$image->id}.path", [UploadedFile::fake()->image('new.png')])
            ->call('save')
            ->assertHasFormErrors(["images.record-{$image->id}.path"]);

        $this->assertSame('orphan', Storage::disk('public')->get($path));
        $this->assertSame('products/old.jpg', $image->fresh()->path);
        Storage::disk('public')->assertExists('products/old.jpg');
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
                    'images' => [['path' => [$file], 'filename_description' => 'frente']],
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
        $image = $product->images()->create(['path' => 'products/first.jpg', 'position' => 1, 'filename_description' => 'frente']);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->set('data.images', ["record-{$image->id}" => ['path' => ['other/private.txt'], 'filename_description' => 'frente']])
            ->call('save')
            ->assertHasFormErrors(["images.record-{$image->id}.path"]);

        $this->assertDatabaseHas('product_images', ['id' => $image->id, 'path' => 'products/first.jpg']);
        Storage::disk('public')->assertExists('other/private.txt');
    }
}
