<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Category;
use App\Models\Product;
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

    public function test_mp4_video_is_stored_unchanged(): void
    {
        $product = Product::factory()->create(['name' => 'Producto']);
        $directory = 'products/'.$product->image_directory;
        $input = fopen('php://temp', 'w+b');
        fwrite($input, 'contenido-original');
        rewind($input);

        $path = app(ProductVideoStorage::class)->store($input, 'public', $directory, $product->name, 'Vista general', 'mp4');
        fclose($input);

        $this->assertSame("{$directory}/producto-vista-general.mp4", $path);
        $this->assertSame('contenido-original', Storage::disk('public')->get($path));
        $this->assertSame([$path], Storage::disk('public')->allFiles($directory));

        $video = $product->videos()->create(['path' => $path, 'position' => 1, 'filename_description' => 'Vista general']);
        $this->assertSame(Storage::disk('public')->url($path), $video->url);
        $products = collect($this->getJson('/api/products/index')->assertOk()->json())->keyBy('id');
        $this->assertSame($video->url, $products[$product->id]['video_link']);

        $video->delete();
        Storage::disk('public')->assertMissing($path);
    }

    public function test_other_formats_are_converted_to_a_single_mp4(): void
    {
        $product = Product::factory()->create(['name' => 'Producto']);
        $directory = 'products/'.$product->image_directory;
        $inputPath = tempnam(sys_get_temp_dir(), 'video-mov-');
        (new Process([
            'ffmpeg', '-v', 'error', '-f', 'lavfi', '-i', 'testsrc2=s=320x240:r=8:d=1',
            '-c:v', 'libx264', '-pix_fmt', 'yuv420p', '-f', 'mov', '-y', $inputPath,
        ]))->mustRun();

        try {
            $input = fopen($inputPath, 'rb');
            $path = app(ProductVideoStorage::class)->store($input, 'public', $directory, $product->name, 'Vista general', 'mov');
            fclose($input);
        } finally {
            unlink($inputPath);
        }

        $this->assertSame("{$directory}/producto-vista-general.mp4", $path);
        $this->assertSame([$path], Storage::disk('public')->allFiles($directory));
        $this->assertSame([], glob(sys_get_temp_dir().'/product-video-*.mp4') ?: []);
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

    public function test_filament_uploads_and_replaces_mp4_videos(): void
    {
        $product = Product::factory()->create(['name' => 'Producto']);
        $directory = "products/{$product->image_directory}";

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm([
                'videos' => [
                    ['filename_description' => 'frente', 'path' => [UploadedFile::fake()->createWithContent('video.mp4', 'uno')]],
                    ['filename_description' => 'dorso', 'path' => [UploadedFile::fake()->createWithContent('second.mp4', 'dos')]],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(
            ["{$directory}/producto-frente.mp4", "{$directory}/producto-dorso.mp4"],
            $product->videos()->pluck('path')->all(),
        );
        $this->assertEqualsCanonicalizing(
            ["{$directory}/producto-frente.mp4", "{$directory}/producto-dorso.mp4"],
            Storage::disk('public')->allFiles($directory),
        );
        $this->assertSame('uno', Storage::disk('public')->get("{$directory}/producto-frente.mp4"));

        $video = $product->videos()->firstOrFail();
        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->set("data.videos.record-{$video->id}.path", [UploadedFile::fake()->createWithContent('video.mp4', 'nuevo')])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame("{$directory}/producto-frente.mp4", $video->fresh()->path);
        $this->assertSame('nuevo', Storage::disk('public')->get("{$directory}/producto-frente.mp4"));
        $this->assertCount(2, Storage::disk('public')->allFiles($directory));
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

    public function test_video_conflicts_with_existing_files_and_other_videos(): void
    {
        $product = Product::factory()->create(['name' => 'Producto']);
        $directory = 'products/'.$product->image_directory;
        $storage = app(ProductVideoStorage::class);

        $videoPath = "{$directory}/producto-otro.mp4";
        $product->videos()->create(['path' => $videoPath, 'position' => 1, 'filename_description' => 'otro']);
        $this->assertTrue($storage->hasConflict('public', $videoPath));
        $this->assertFalse($storage->hasConflict('public', $videoPath, $videoPath));

        $imagePath = "{$directory}/producto-foto.webp";
        $product->images()->create(['path' => $imagePath, 'position' => 1, 'filename_description' => 'foto']);
        $this->assertTrue($storage->hasConflict('public', $imagePath));
    }
}
