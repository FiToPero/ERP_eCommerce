<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVideo;
use App\Models\User;
use App\Services\ProductVideoStorage;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class ProductVideosTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        config(['filesystems.product_images_disk' => 'public']);
        Storage::fake('public');
        Gate::before(fn () => true);
        $this->actingAs(User::factory()->create());
    }

    public function test_video_conversion_uses_product_directory_and_named_variants(): void
    {
        $product = Product::factory()->create(['name' => 'Producto']);
        $directory = 'products/'.$product->image_directory;
        $inputPath = tempnam(sys_get_temp_dir(), 'video-test-');
        $this->assertNotFalse($inputPath);

        try {
            $process = new Process([
                'ffmpeg', '-v', 'error', '-f', 'lavfi', '-i', 'color=c=red:s=64x64:r=1:d=1',
                '-c:v', 'libx264', '-pix_fmt', 'yuv420p', '-f', 'mp4', '-y', $inputPath,
            ]);
            $process->mustRun();
            $input = fopen($inputPath, 'rb');
            $this->assertIsResource($input);
            try {
                $path = app(ProductVideoStorage::class)->store($input, 'public', $directory, $product->name, 'Vista general');
            } finally {
                fclose($input);
            }

            $this->assertSame("{$directory}/producto-vista-general.mp4", $path);
            $video = $product->videos()->create(['path' => $path, 'position' => 1, 'filename_description' => 'Vista general']);
            foreach (ProductVideo::filePaths($path) as $filePath) {
                Storage::disk('public')->assertExists($filePath);
            }
            $this->assertSame(Storage::disk('public')->url("{$directory}/producto-vista-general-ecommerce-16x9.mp4"), $video->ecommerce_url);
            $this->assertSame(Storage::disk('public')->url("{$directory}/producto-vista-general-thumbnail.webp"), $video->thumbnail_url);
            $products = collect($this->getJson('/api/products/index')->assertOk()->json())->keyBy('id');
            $this->assertSame($video->ecommerce_url, $products[$product->id]['video_link']);
            $this->assertSame($video->thumbnail_url, $products[$product->id]['videos'][0]['thumbnail_url']);

            $imagePath = "{$directory}/other.webp";
            Storage::disk('public')->put($imagePath, 'image');
            $video->delete();
            foreach (ProductVideo::filePaths($path) as $filePath) {
                Storage::disk('public')->assertMissing($filePath);
            }
            Storage::disk('public')->assertExists($imagePath);
        } finally {
            if (is_file($inputPath)) {
                unlink($inputPath);
            }
        }
    }

    public function test_form_requires_distinct_video_descriptions_on_create_and_update(): void
    {
        $category = Category::factory()->create();
        $video = fn () => UploadedFile::fake()->create('video.mp4', 10, 'video/mp4');

        foreach (['', '!!!'] as $description) {
            Livewire::test(CreateProduct::class)
                ->fillForm([
                    'category_id' => $category->id,
                    'name' => 'Producto',
                    'slug' => 'producto',
                    'sku' => 'PROD-1',
                    'short_description' => 'Descripción',
                    'condition' => 'new',
                    'videos' => [['filename_description' => $description, 'path' => [$video()]]],
                ])
                ->call('create')
                ->assertHasFormErrors(['videos.0.filename_description']);
        }

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'category_id' => $category->id,
                'name' => 'Producto',
                'slug' => 'producto',
                'sku' => 'PROD-1',
                'short_description' => 'Descripción',
                'condition' => 'new',
                'videos' => [
                    ['filename_description' => 'Vista general', 'path' => [$video()]],
                    ['filename_description' => 'vista-general', 'path' => [$video()]],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['videos.0.filename_description', 'videos.1.filename_description']);

        $product = Product::factory()->create();
        $firstPath = "products/{$product->image_directory}/one.mp4";
        $secondPath = "products/{$product->image_directory}/two.mp4";
        Storage::disk('public')->put($firstPath, 'one');
        Storage::disk('public')->put($secondPath, 'two');
        $product->videos()->create(['path' => $firstPath, 'position' => 1, 'filename_description' => 'frente']);
        $second = $product->videos()->create(['path' => $secondPath, 'position' => 2, 'filename_description' => 'dorso']);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->set("data.videos.record-{$second->id}.filename_description", 'Frente')
            ->call('save')
            ->assertHasFormErrors(["videos.record-{$second->id}.filename_description"]);
        $this->assertSame('dorso', $second->fresh()->filename_description);
    }

    public function test_form_rejects_collisions_between_new_media_files(): void
    {
        $category = Category::factory()->create();
        $data = [
            'category_id' => $category->id,
            'name' => 'Producto',
            'slug' => 'producto',
            'sku' => 'PROD-1',
            'short_description' => 'Descripción',
            'condition' => 'new',
        ];
        $video = fn () => UploadedFile::fake()->create('video.mp4', 10, 'video/mp4');

        Livewire::test(CreateProduct::class)
            ->fillForm($data + [
                'images' => [['filename_description' => 'frente-thumbnail', 'path' => [UploadedFile::fake()->image('photo.jpg')]]],
                'videos' => [['filename_description' => 'frente', 'path' => [$video()]]],
            ])
            ->call('create')
            ->assertHasFormErrors(['videos.0.filename_description']);

        Livewire::test(CreateProduct::class)
            ->fillForm($data + [
                'videos' => [
                    ['filename_description' => 'frente', 'path' => [$video()]],
                    ['filename_description' => 'frente-ecommerce-16x9', 'path' => [$video()]],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['videos.0.filename_description']);

        $this->assertDatabaseCount('product_videos', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('products'));
    }

    public function test_filament_uploads_and_replaces_video_at_same_path(): void
    {
        $product = Product::factory()->create(['name' => 'Producto']);
        $inputPath = tempnam(sys_get_temp_dir(), 'video-form-');
        $this->assertNotFalse($inputPath);

        try {
            (new Process([
                'ffmpeg', '-v', 'error', '-f', 'lavfi', '-i', 'color=c=red:s=64x64:r=1:d=1',
                '-c:v', 'libx264', '-pix_fmt', 'yuv420p', '-f', 'mp4', '-y', $inputPath,
            ]))->mustRun();

            Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
                ->fillForm([
                    'images' => [
                        ['filename_description' => 'foto', 'path' => [UploadedFile::fake()->image('photo.jpg')]],
                    ],
                    'videos' => [
                        ['filename_description' => 'frente', 'path' => [UploadedFile::fake()->createWithContent('video.mp4', file_get_contents($inputPath))]],
                        ['filename_description' => 'dorso', 'path' => [UploadedFile::fake()->createWithContent('second.mp4', file_get_contents($inputPath))]],
                    ],
                ])
                ->call('save')
                ->assertHasNoFormErrors();

            $video = $product->videos()->firstOrFail();
            $this->assertSame(
                ["products/{$product->image_directory}/producto-frente.mp4", "products/{$product->image_directory}/producto-dorso.mp4"],
                $product->videos()->pluck('path')->all(),
            );
            $path = "products/{$product->image_directory}/producto-frente.mp4";
            $this->assertSame($path, $video->path);
            $this->assertSame('frente', $video->filename_description);
            $imagePath = "products/{$product->image_directory}/producto-foto.webp";
            $this->assertSame($imagePath, $product->images()->firstOrFail()->path);
            Storage::disk('public')->assertExists($imagePath);
            foreach (ProductVideo::filePaths($path) as $filePath) {
                Storage::disk('public')->assertExists($filePath);
            }

            Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
                ->set("data.videos.record-{$video->id}.path", [UploadedFile::fake()->createWithContent('video.mp4', file_get_contents($inputPath))])
                ->call('save')
                ->assertHasNoFormErrors();
            $this->assertSame($path, $video->fresh()->path);
            foreach (ProductVideo::filePaths($path) as $filePath) {
                Storage::disk('public')->assertExists($filePath);
            }
        } finally {
            if (is_file($inputPath)) {
                unlink($inputPath);
            }
        }
    }

    public function test_changes_and_reordering_do_not_rename_videos(): void
    {
        $product = Product::factory()->create(['name' => 'Producto']);
        $path = "products/{$product->image_directory}/producto-frente.mp4";
        Storage::disk('public')->put($path, 'video');
        $video = $product->videos()->create(['path' => $path, 'position' => 1, 'filename_description' => 'frente']);
        $secondPath = "products/{$product->image_directory}/producto-dorso.mp4";
        Storage::disk('public')->put($secondPath, 'video');
        $second = $product->videos()->create(['path' => $secondPath, 'position' => 2, 'filename_description' => 'dorso']);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->set('data.name', 'Otro nombre')
            ->set('data.videos', [
                "record-{$second->id}" => ['filename_description' => 'dorso', 'path' => [$secondPath]],
                "record-{$video->id}" => ['filename_description' => 'detalle', 'path' => [$path]],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($path, $video->fresh()->path);
        $this->assertSame('detalle', $video->fresh()->filename_description);
        $this->assertSame($secondPath, $product->fresh()->videos->first()->path);
        $this->assertSame(2, $video->fresh()->position);
        Storage::disk('public')->assertExists($path);
    }

    public function test_video_conflicts_with_existing_image_thumbnail_and_other_video_variants(): void
    {
        $product = Product::factory()->create(['name' => 'Producto']);
        $directory = 'products/'.$product->image_directory;
        $imagePath = "{$directory}/producto-frente-thumbnail.webp";
        $product->images()->create(['path' => $imagePath, 'position' => 1, 'filename_description' => 'frente-thumbnail']);
        $storage = app(ProductVideoStorage::class);
        $this->assertTrue($storage->hasConflict('public', "{$directory}/producto-frente.mp4"));

        $videoPath = "{$directory}/producto-otro.mp4";
        $product->videos()->create(['path' => $videoPath, 'position' => 1, 'filename_description' => 'otro']);
        $this->assertTrue($storage->hasConflict('public', "{$directory}/producto-otro-ecommerce-16x9.mp4"));
        $this->assertFalse($storage->hasConflict('public', $videoPath, $videoPath));
    }
}
