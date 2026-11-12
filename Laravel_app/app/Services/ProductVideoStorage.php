<?php

namespace App\Services;

use App\Models\ProductImage;
use App\Models\ProductVideo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class ProductVideoStorage
{
    /**
     * @param  resource  $input
     */
    public function store($input, string $disk, string $directory, string $productName, string $description, ?string $existingPath = null): string
    {
        if (! is_resource($input)) {
            throw new RuntimeException('No se pudo leer el video cargado.');
        }

        $targetPath = $this->path($directory, $productName, $description);

        $temporaryDirectory = sys_get_temp_dir().'/product-video-'.Str::ulid();
        if (! mkdir($temporaryDirectory, 0700)) {
            throw new RuntimeException('No se pudo preparar la conversión del video.');
        }

        $source = $temporaryDirectory.'/source';
        $deadline = (defined('LARAVEL_START') ? LARAVEL_START : microtime(true)) + 1500;

        try {
            $output = fopen($source, 'wb');
            if ($output === false) {
                throw new RuntimeException('No se pudo preparar el archivo temporal del video.');
            }

            try {
                if (stream_copy_to_stream($input, $output) === false) {
                    throw new RuntimeException('No se pudo leer el video cargado.');
                }
            } finally {
                fclose($output);
            }

            $this->convert($source, $temporaryDirectory.'/original.mp4', ['-vf', 'scale=ceil(iw/2)*2:ceil(ih/2)*2'], 0, $deadline);

            foreach ([
                'ecommerce-16x9.mp4' => [1920, 1080],
                'vertical-9x16.mp4' => [1080, 1920],
                'square-1x1.mp4' => [1080, 1080],
            ] as $filename => [$width, $height]) {
                $filter = "scale={$width}:{$height}:force_original_aspect_ratio=increase,crop={$width}:{$height}";
                $this->convert($temporaryDirectory.'/original.mp4', $temporaryDirectory.'/'.$filename, ['-vf', $filter], 21, $deadline);
            }

            $this->run([
                'ffmpeg', '-nostdin', '-v', 'error', '-i', $temporaryDirectory.'/original.mp4',
                '-frames:v', '1', '-c:v', 'libwebp', '-y', $temporaryDirectory.'/thumbnail.webp',
            ], $deadline);

            Cache::lock('product-media:'.hash('sha256', $disk.':'.$directory), 120)->block(10, function () use ($disk, $targetPath, $existingPath, $temporaryDirectory): void {
                if ($this->hasConflict($disk, $targetPath, $existingPath)) {
                    throw new RuntimeException("Ya existe un archivo con el nombre del video {$targetPath}.");
                }

                $this->writeFiles($disk, $targetPath, $existingPath, $temporaryDirectory);
            });

            return $targetPath;
        } finally {
            foreach (array_merge(['source'], array_keys(ProductVideo::FILES)) as $filename) {
                $path = $temporaryDirectory.'/'.$filename;
                if (is_file($path) && ! unlink($path)) {
                    throw new RuntimeException("No se pudo eliminar el archivo temporal: {$path}");
                }
            }
            if (! rmdir($temporaryDirectory)) {
                throw new RuntimeException('No se pudo eliminar el directorio temporal del video.');
            }
        }
    }

    public function path(string $directory, string $productName, string $description): string
    {
        $imagePath = app(ProductImageStorage::class)->path($directory, $productName, $description);

        return substr($imagePath, 0, -5).'.mp4';
    }

    public function hasConflict(string $disk, string $path, ?string $existingPath = null): bool
    {
        $paths = ProductVideo::filePaths($path);
        $ownPaths = $path === $existingPath ? array_values(ProductVideo::filePaths($existingPath)) : [];

        foreach ($paths as $target) {
            if (! in_array($target, $ownPaths, true) && Storage::disk($disk)->exists($target)) {
                return true;
            }
        }

        if (ProductImage::query()->whereIn('path', $paths)->exists()) {
            return true;
        }

        return ProductVideo::query()
            ->where('path', 'like', dirname($path).'/%')
            ->when($existingPath, fn ($query) => $query->where('path', '!=', $existingPath))
            ->pluck('path')
            ->contains(fn (string $other): bool => array_intersect($paths, ProductVideo::filePaths($other)) !== []);
    }

    private function writeFiles(string $disk, string $path, ?string $existingPath, string $temporaryDirectory): void
    {
        $storage = Storage::disk($disk);
        $backups = [];
        $written = [];

        try {
            foreach (ProductVideo::filePaths($path) as $filename => $target) {
                if ($path === $existingPath && $storage->exists($target)) {
                    $backup = tmpfile();
                    $original = $storage->readStream($target);
                    if ($backup === false) {
                        if (is_resource($original)) {
                            fclose($original);
                        }
                        throw new RuntimeException("No se pudo respaldar el archivo de video: {$target}");
                    }
                    if ($original === false) {
                        fclose($backup);
                        throw new RuntimeException("No se pudo respaldar el archivo de video: {$target}");
                    }
                    $backups[$target] = $backup;
                    try {
                        if (stream_copy_to_stream($original, $backup) === false) {
                            throw new RuntimeException("No se pudo respaldar el archivo de video: {$target}");
                        }
                    } finally {
                        fclose($original);
                    }
                }

                $stream = fopen($temporaryDirectory.'/'.$filename, 'rb');
                if ($stream === false) {
                    throw new RuntimeException("No se pudo leer el archivo convertido: {$filename}");
                }

                try {
                    $written[] = $target;
                    if (! $storage->put($target, $stream, 'public')) {
                        throw new RuntimeException("No se pudo guardar el archivo de video: {$filename}");
                    }
                } finally {
                    fclose($stream);
                }
            }
        } catch (\Throwable $exception) {
            foreach ($written as $target) {
                if (isset($backups[$target])) {
                    rewind($backups[$target]);
                    if (! $storage->put($target, $backups[$target], 'public')) {
                        throw new RuntimeException("No se pudo restaurar el archivo de video: {$target}", previous: $exception);
                    }
                } elseif (! $storage->delete($target)) {
                    throw new RuntimeException("No se pudo limpiar el archivo de video: {$target}", previous: $exception);
                }
            }

            throw $exception;
        } finally {
            foreach ($backups as $backup) {
                fclose($backup);
            }
        }
    }

    private function convert(string $input, string $output, array $filters, int $quality, float $deadline): void
    {
        $this->run([
            'ffmpeg', '-nostdin', '-v', 'error', '-protocol_whitelist', 'file,pipe',
            '-format_whitelist', 'mov,matroska,webm,avi', '-i', $input,
            '-map', '0:v:0', '-map', '0:a:0?', ...$filters,
            '-c:v', 'libx264', '-threads', '2', '-preset', 'medium', '-crf', (string) $quality,
            '-pix_fmt', 'yuv420p', '-c:a', 'aac', '-movflags', '+faststart', '-y', $output,
        ], $deadline);
    }

    private function run(array $command, float $deadline): void
    {
        $remaining = $deadline - microtime(true);
        if ($remaining <= 0) {
            throw new RuntimeException('La conversión del video superó el tiempo permitido.');
        }

        $process = new Process($command, timeout: $remaining);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('No se pudo convertir el video: '.trim($process->getErrorOutput()));
        }
    }
}
