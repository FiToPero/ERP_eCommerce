<?php

namespace App\Services;

use App\Models\ProductImage;
use App\Models\ProductVideo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class ProductVideoStorage
{
    private const MIME_EXTENSIONS = [
        'video/mp4' => 'mp4',
        'video/quicktime' => 'mov',
        'video/webm' => 'webm',
        'video/x-matroska' => 'mkv',
        'video/x-msvideo' => 'avi',
    ];

    /**
     * Guarda un único MP4: copia el archivo si ya es MP4 y, si no, lo convierte una vez.
     *
     * @param  resource  $input
     */
    public function store($input, string $disk, string $directory, string $productName, string $description, string $extension, ?string $existingPath = null): string
    {
        if (! is_resource($input)) {
            throw new RuntimeException('No se pudo leer el video cargado.');
        }

        $targetPath = $this->path($directory, $productName, $description);

        $temporary = null;
        if ($extension !== 'mp4') {
            $temporary = $this->convertToMp4($input, $extension);
            $input = fopen($temporary, 'rb');
            if ($input === false) {
                unlink($temporary);
                throw new RuntimeException('No se pudo leer el video convertido.');
            }
        }

        try {
            Cache::lock('product-media:'.hash('sha256', $disk.':'.$directory), 120)->block(10, function () use ($input, $disk, $targetPath, $existingPath): void {
                if ($this->hasConflict($disk, $targetPath, $existingPath)) {
                    throw new RuntimeException("Ya existe un archivo con el nombre del video {$targetPath}.");
                }

                $this->write($disk, $targetPath, $input, $targetPath === $existingPath);
            });
        } finally {
            if ($temporary !== null) {
                fclose($input);
                unlink($temporary);
            }
        }

        return $targetPath;
    }

    /**
     * Convierte a MP4 (H.264/AAC) en una sola pasada y devuelve la ruta temporal.
     *
     * @param  resource  $input
     */
    private function convertToMp4($input, string $extension): string
    {
        $source = tempnam(sys_get_temp_dir(), 'product-video-src-');
        $output = sys_get_temp_dir().'/product-video-'.Str::ulid().'.mp4';

        try {
            $target = fopen($source, 'wb');
            if ($target === false || stream_copy_to_stream($input, $target) === false) {
                throw new RuntimeException('No se pudo leer el video cargado.');
            }
            fclose($target);

            $process = new Process([
                'ffmpeg', '-nostdin', '-v', 'error', '-protocol_whitelist', 'file,pipe',
                '-f', ['mov' => 'mov,mp4', 'webm' => 'matroska,webm', 'mkv' => 'matroska,webm', 'avi' => 'avi'][$extension] ?? 'mov,mp4',
                '-i', $source, '-map', '0:v:0', '-map', '0:a:0?',
                '-vf', 'scale=ceil(iw/2)*2:ceil(ih/2)*2',
                '-c:v', 'libx264', '-preset', 'veryfast', '-crf', '23', '-pix_fmt', 'yuv420p',
                '-c:a', 'aac', '-movflags', '+faststart', '-y', $output,
            ], timeout: 1500);
            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException('No se pudo convertir el video: '.trim($process->getErrorOutput()));
            }
        } catch (\Throwable $exception) {
            if (is_file($output)) {
                unlink($output);
            }

            throw $exception;
        } finally {
            if (is_file($source)) {
                unlink($source);
            }
        }

        return $output;
    }

    // Formato de entrada: extensión original o, si no es válida, el tipo MIME.
    public function extension(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, ProductVideo::EXTENSIONS, true)) {
            return $extension;
        }

        return self::MIME_EXTENSIONS[$file->getMimeType()] ?? throw new RuntimeException('El formato del video no es válido.');
    }

    public function path(string $directory, string $productName, string $description): string
    {
        $imagePath = app(ProductImageStorage::class)->path($directory, $productName, $description);

        return substr($imagePath, 0, -5).'.mp4';
    }

    public function hasConflict(string $disk, string $path, ?string $existingPath = null): bool
    {
        if ($path !== $existingPath && Storage::disk($disk)->exists($path)) {
            return true;
        }

        if (ProductImage::query()->where('path', $path)->exists()) {
            return true;
        }

        return ProductVideo::query()
            ->where('path', $path)
            ->when($existingPath, fn ($query) => $query->where('path', '!=', $existingPath))
            ->exists();
    }

    /**
     * @param  resource  $input
     */
    private function write(string $disk, string $path, $input, bool $replacing): void
    {
        $storage = Storage::disk($disk);
        $backup = null;

        try {
            if ($replacing && $storage->exists($path)) {
                $backup = tmpfile();
                $original = $storage->readStream($path);
                if ($backup === false || $original === false) {
                    if (is_resource($original)) {
                        fclose($original);
                    }
                    throw new RuntimeException("No se pudo respaldar el archivo de video: {$path}");
                }

                try {
                    stream_copy_to_stream($original, $backup);
                } finally {
                    fclose($original);
                }
            }

            if (! $storage->put($path, $input, 'public')) {
                throw new RuntimeException("No se pudo guardar el archivo de video: {$path}");
            }
        } catch (\Throwable $exception) {
            if (is_resource($backup)) {
                rewind($backup);
                $storage->put($path, $backup, 'public');
            } else {
                $storage->delete($path);
            }

            throw $exception;
        } finally {
            if (is_resource($backup)) {
                fclose($backup);
            }
        }
    }
}
