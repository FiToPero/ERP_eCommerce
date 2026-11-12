<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProductVideo extends Model
{
    public const FILES = [
        'original.mp4' => '.mp4',
        'ecommerce-16x9.mp4' => '-ecommerce-16x9.mp4',
        'vertical-9x16.mp4' => '-vertical-9x16.mp4',
        'square-1x1.mp4' => '-square-1x1.mp4',
        'thumbnail.webp' => '-thumbnail.webp',
    ];

    protected $fillable = ['path', 'position', 'filename_description', 'title', 'description'];

    protected $appends = ['original_url', 'ecommerce_url', 'vertical_url', 'square_url', 'thumbnail_url'];

    protected static function booted(): void
    {
        static::updated(function (ProductVideo $video): void {
            if ($video->wasChanged('path')) {
                self::deleteFilesAfterCommit($video->getRawOriginal('path'), $video);
            }
        });

        static::deleted(function (ProductVideo $video): void {
            self::deleteFilesAfterCommit($video->path, $video);
        });
    }

    public static function disk(): string
    {
        return ProductImage::disk();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getOriginalUrlAttribute(): string
    {
        return $this->fileUrl('original.mp4');
    }

    public function getEcommerceUrlAttribute(): string
    {
        return $this->fileUrl('ecommerce-16x9.mp4');
    }

    public function getVerticalUrlAttribute(): string
    {
        return $this->fileUrl('vertical-9x16.mp4');
    }

    public function getSquareUrlAttribute(): string
    {
        return $this->fileUrl('square-1x1.mp4');
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->fileUrl('thumbnail.webp');
    }

    private function fileUrl(string $filename): string
    {
        return Storage::disk(self::disk())->url(self::filePaths($this->path)[$filename]);
    }

    public static function filePaths(string $path): array
    {
        if (! str_ends_with($path, '.mp4')) {
            throw new RuntimeException("La ruta del video no es válida: {$path}");
        }

        $base = substr($path, 0, -4);

        return array_map(fn (string $suffix): string => $base.$suffix, self::FILES);
    }

    public static function deleteFilesAfterCommit(string $path, Model $model): void
    {
        $disk = self::disk();

        $model->getConnection()->afterCommit(function () use ($path, $disk): void {
            if (self::query()->where('path', $path)->exists()) {
                return;
            }

            foreach (self::filePaths($path) as $filePath) {
                if (! Storage::disk($disk)->delete($filePath)) {
                    throw new RuntimeException("No se pudo eliminar el video del disco {$disk}: {$filePath}");
                }
            }
        });
    }
}
