<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoryDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_soft_delete_and_restore_cascade_only_to_categories_deleted_by_the_parent(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);
        $grandchild = Category::factory()->create(['parent_id' => $child->id]);
        $previouslyDeleted = Category::factory()->create(['parent_id' => $parent->id]);
        $previouslyDeleted->delete();
        $product = Product::factory()->create(['category_id' => $grandchild->id]);

        $parent->delete();

        $this->assertSoftDeleted($parent);
        $this->assertSoftDeleted($child);
        $this->assertSoftDeleted($grandchild);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'category_id' => $grandchild->id, 'deleted_at' => null]);

        $parent->restore();

        $this->assertNotSoftDeleted($parent);
        $this->assertNotSoftDeleted($child);
        $this->assertNotSoftDeleted($grandchild);
        $this->assertSoftDeleted($previouslyDeleted);
    }

    public function test_force_delete_removes_subcategories_but_keeps_products_and_media(): void
    {
        Storage::fake(ProductImage::disk());
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);
        $grandchild = Category::factory()->create(['parent_id' => $child->id]);
        $product = Product::factory()->create(['category_id' => $grandchild->id]);
        $disk = config('filesystems.product_images_disk');
        $path = "products/{$product->image_directory}/image.webp";
        $videoPath = "products/{$product->image_directory}/video.mp4";
        Storage::disk($disk)->put($path, 'image');
        Storage::disk($disk)->put($videoPath, 'video');
        $product->images()->create(['path' => $path, 'position' => 1, 'filename_description' => 'image']);
        $product->videos()->create(['path' => $videoPath, 'position' => 1, 'filename_description' => 'video']);

        $parent->forceDelete();

        $this->assertDatabaseMissing('categories', ['id' => $parent->id]);
        $this->assertDatabaseMissing('categories', ['id' => $child->id]);
        $this->assertDatabaseMissing('categories', ['id' => $grandchild->id]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'category_id' => null]);
        $this->assertDatabaseHas('product_images', ['product_id' => $product->id, 'path' => $path]);
        $this->assertDatabaseHas('product_videos', ['product_id' => $product->id, 'path' => $videoPath]);
        Storage::disk($disk)->assertExists($path);
        Storage::disk($disk)->assertExists($videoPath);
    }

    public function test_soft_deletion_is_rolled_back_with_its_descendants(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        DB::beginTransaction();
        $parent->delete();
        DB::rollBack();

        $this->assertNotSoftDeleted($parent);
        $this->assertNotSoftDeleted($child);
    }

    public function test_migration_upgrades_a_previously_existing_category_foreign_key(): void
    {
        $migration = require database_path('migrations/2026_11_14_000001_preserve_products_when_categories_are_deleted.php');
        $migration->down();

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $migration->up();
        $category->forceDelete();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'category_id' => null]);
    }
}
