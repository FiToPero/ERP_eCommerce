<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProductVideo extends Model
{
    public const EXTENSIONS = ['mp4', 'mov', 'webm', 'mkv', 'avi'];

    protected $fillable = ['path', 'position', 'filename_description', 'title', 'description'];

    protected $appends = ['url'];

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

    public function getUrlAttribute(): string
    {
        return Storage::disk(self::disk())->url($this->path);
    }

    public static function filePaths(string $path): array
    {
        if (! in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::EXTENSIONS, true)) {
            throw new RuntimeException("La ruta del video no es válida: {$path}");
        }

        return [$path];
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
