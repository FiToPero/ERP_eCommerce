<?php

namespace App\Services;

use GdImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProductImageStorage
{
    /**
     * @param  resource  $input
     */
    public function store($input, string $disk, string $directory, string $productName, ?string $description = null): string
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

            $path = $this->reservePath($disk, $directory, $productName, $description);
            rewind($output);
            if (! Storage::disk($disk)->put($path, $output, 'public')) {
                throw new RuntimeException('No se pudo guardar la imagen del producto.');
            }

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

    private function reservePath(string $disk, string $directory, string $productName, ?string $description): string
    {
        if (! preg_match('/^products\/[a-z0-9]+(?:-[a-z0-9]+)*-[0-9a-hjkmnp-tv-z]{26}$/', $directory)) {
            throw new RuntimeException('El directorio de imágenes del producto no es válido.');
        }

        $name = trim(substr(Str::slug($productName), 0, 65), '-') ?: 'producto';
        $description = trim(substr(Str::slug($description ?? ''), 0, 50), '-') ?: 'imagen';
        $base = "{$directory}/{$name}-{$description}";

        for ($number = 1; $number <= 10000; $number++) {
            $path = $base.($number === 1 ? '' : "-{$number}").'.webp';

            if (! DB::table('product_image_names')->insertOrIgnore(['path' => $path])) {
                continue;
            }

            if (! Storage::disk($disk)->exists($path)) {
                return $path;
            }
        }

        throw new RuntimeException('No se pudo reservar un nombre para la imagen.');
    }
}
