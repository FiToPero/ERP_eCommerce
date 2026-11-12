<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProductImage extends Model
{
    protected $fillable = ['path', 'position', 'filename_description', 'alt_text', 'title', 'description'];

    protected static function booted(): void
    {
        static::updated(function (ProductImage $image): void {
            if ($image->wasChanged('path')) {
                self::deleteFileAfterCommit($image->getRawOriginal('path'), $image);
            }
        });

        static::deleted(function (ProductImage $image): void {
            self::deleteFileAfterCommit($image->path, $image);
        });
    }

    public static function disk(): string
    {
        return config('filesystems.product_images_disk');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk(self::disk())->url($this->path);
    }

    public static function deleteFileAfterCommit(string $path, Model $model): void
    {
        $disk = self::disk();

        $model->getConnection()->afterCommit(function () use ($path, $disk): void {
            if (self::query()->where('path', $path)->exists()) {
                return;
            }

            if (! Storage::disk($disk)->delete($path)) {
                throw new RuntimeException("No se pudo eliminar la imagen del disco {$disk}: {$path}");
            }
        });
    }
}
