<?php

namespace App\Filament\Forms\Components;

use App\Models\ProductImage;
use App\Services\ProductImageStorage;
use Closure;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductImageUpload extends FileUpload
{
    protected string|Closure $productName = '';

    protected string|Closure|null $filenameDescription = null;

    public function productName(string|Closure $name): static
    {
        $this->productName = $name;

        return $this;
    }

    public function filenameDescription(string|Closure|null $description): static
    {
        $this->filenameDescription = $description;

        return $this;
    }

    protected function setUp(): void
    {
        // Conserva primero la configuración predeterminada de FileUpload.
        parent::setUp();

        // Define cómo guardar cada archivo temporal que se suba desde este campo.
        // La función recibe el archivo temporal de Livewire y debe devolver su ruta.
        $this->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
            $input = $file->readStream();

            try {
                $path = app(ProductImageStorage::class)->store(
                    $input,
                    $this->getDiskName(),
                    $this->getDirectory(),
                    (string) $this->evaluate($this->productName),
                    $this->evaluate($this->filenameDescription),
                );
            } finally {
                if (is_resource($input)) {
                    fclose($input);
                }
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
