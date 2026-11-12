<?php

namespace App\Services;

use App\Models\ProductImage;
use App\Models\ProductVideo;
use GdImage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProductImageStorage
{
    /**
     * @param  resource  $input
     */
    public function store($input, string $disk, string $directory, string $productName, string $description, ?string $existingPath = null): string
    {
        if (! function_exists('imagewebp') || ! (gd_info()['WebP Support'] ?? false)) {
            throw new RuntimeException('El servidor no dispone de soporte WebP en GD.');
        }

        if (! is_resource($input)) {
            throw new RuntimeException('No se pudo leer la imagen cargada.');
        }

        $source = tmpfile();
        $output = tmpfile();

        if ($source === false || $output === false) {
            throw new RuntimeException('No se pudo preparar la conversión de la imagen.');
        }

        try {
            if (stream_copy_to_stream($input, $source) === false) {
                throw new RuntimeException('No se pudo leer la imagen cargada.');
            }

            $sourcePath = stream_get_meta_data($source)['uri'];
            $size = getimagesize($sourcePath);

            if ($size === false || $size[0] * $size[1] > 12000000) {
                throw new RuntimeException('La imagen no es válida o supera los 12 megapíxeles.');
            }

            $image = match ($size[2]) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
                IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
                IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
                default => false,
            };

            if (! $image instanceof GdImage) {
                throw new RuntimeException('No se pudo decodificar la imagen cargada.');
            }

            try {
                if ($size[2] === IMAGETYPE_JPEG) {
                    $image = $this->orientImage($image, $sourcePath);
                }

                imagepalettetotruecolor($image);
                imagealphablending($image, false);
                imagesavealpha($image, true);

                $outputPath = stream_get_meta_data($output)['uri'];
                if (! imagewebp($image, $outputPath, 85)) {
                    throw new RuntimeException('No se pudo convertir la imagen a WebP.');
                }

                $converted = getimagesize($outputPath);
                if ($converted === false || $converted[2] !== IMAGETYPE_WEBP) {
                    throw new RuntimeException('La conversión no produjo una imagen WebP válida.');
                }
            } finally {
                imagedestroy($image);
            }

            $path = $this->path($directory, $productName, $description);
            Cache::lock('product-media:'.hash('sha256', $disk.':'.$directory), 120)->block(10, function () use ($disk, $path, $existingPath, $output): void {
                if ($this->hasConflict($disk, $path, $existingPath)) {
                    throw new RuntimeException("Ya existe un archivo de imagen con el nombre {$path}.");
                }

                rewind($output);
                if (! Storage::disk($disk)->put($path, $output, 'public')) {
                    throw new RuntimeException('No se pudo guardar la imagen del producto.');
                }
            });

            return $path;
        } finally {
            fclose($source);
            fclose($output);
        }
    }

    private function orientImage(GdImage $image, string $path): GdImage
    {
        $exif = exif_read_data($path);
        $orientation = is_array($exif) ? ($exif['Orientation'] ?? 1) : 1;

        if (in_array($orientation, [2, 4, 5, 7], true)) {
            $mode = $orientation === 4 ? IMG_FLIP_VERTICAL : IMG_FLIP_HORIZONTAL;
            if (! imageflip($image, $mode)) {
                throw new RuntimeException('No se pudo orientar la imagen.');
            }
        }

        $angle = match ($orientation) {
            3 => 180,
            5, 8 => 90,
            6, 7 => -90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);
        if (! $rotated instanceof GdImage) {
            throw new RuntimeException('No se pudo orientar la imagen.');
        }

        imagedestroy($image);

        return $rotated;
    }

    public static function descriptionSlug(string $description): string
    {
        return trim(substr(Str::slug($description), 0, 50), '-');
    }

    public function hasConflict(string $disk, string $path, ?string $existingPath = null): bool
    {
        if ($path !== $existingPath && (Storage::disk($disk)->exists($path) || ProductImage::query()->where('path', $path)->exists())) {
            return true;
        }

        return ProductVideo::query()->where('path', 'like', dirname($path).'/%')
            ->pluck('path')
            ->contains(fn (string $video): bool => in_array($path, ProductVideo::filePaths($video), true));
    }

    public function path(string $directory, string $productName, string $description): string
    {
        if (! preg_match('/^products\/[a-z0-9]+(?:-[a-z0-9]+)*-[0-9a-hjkmnp-tv-z]{26}$/', $directory)) {
            throw new RuntimeException('El directorio de imágenes del producto no es válido.');
        }

        $name = trim(substr(Str::slug($productName), 0, 65), '-') ?: 'producto';
        $description = self::descriptionSlug($description);
        if ($description === '') {
            throw new RuntimeException('La descripción para el nombre del archivo no es válida.');
        }

        return "{$directory}/{$name}-{$description}.webp";
    }
}
