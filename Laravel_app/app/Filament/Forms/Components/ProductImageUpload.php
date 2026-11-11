<?php

namespace App\Filament\Forms\Components;

use App\Models\ProductImage;
use Closure;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;

class ProductImageUpload extends FileUpload
{
    protected function setUp(): void
    {
        // Conserva primero la configuración predeterminada de FileUpload.
        parent::setUp();

        // Define cómo guardar cada archivo temporal que se suba desde este campo.
        // La función recibe el archivo temporal de Livewire y debe devolver su ruta.
        $this->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
            // Guarda el archivo con visibilidad pública y obtiene su ruta relativa.
            $path = $file->storePubliclyAs(
                // Usa el directorio configurado para el campo, por ejemplo "products".
                $this->getDirectory(),
                // Obtiene el nombre configurado para el archivo que se va a guardar.
                $this->getUploadedFileNameForStorage($file),
                // Usa el disco de almacenamiento configurado para este campo.
                $this->getDiskName(),
            );

            if (! is_string($path)) {
                throw new RuntimeException('No se pudo guardar la imagen del producto.');
            }

            // Registra en la sesión que esta ruta fue subida por el usuario actual.
            // El hash se usa como clave; el valor guardado sigue siendo la ruta original.
            session()->put('product_image_uploads.'.hash('sha256', $path), $path);

            // Devuelve la ruta para que Filament la incorpore al estado del formulario.
            return $path;
        });
    }

    public function getValidationRules(): array
    {
        return [
            ...parent::getValidationRules(),
            function (string $attribute, mixed $value, Closure $fail): void {
                if (! is_array($value)) {
                    return;
                }

                $record = $this->getRecord();
                foreach ($value as $file) {
                    if ($file instanceof TemporaryUploadedFile) {
                        continue;
                    }

                    if ($record instanceof ProductImage && $file === $record->path) {
                        continue;
                    }

                    if (is_string($file)
                        && session()->get('product_image_uploads.'.hash('sha256', $file)) === $file
                        && Storage::disk($this->getDiskName())->exists($file)) {
                        continue;
                    }

                    $fail('La imagen seleccionada no es válida.');
                }
            },
        ];
    }
}
