<?php

namespace App\Filament\Forms\Components;

use App\Models\ProductVideo;
use App\Services\ProductImageStorage;
use App\Services\ProductVideoStorage;
use Closure;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductVideoUpload extends FileUpload
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
        parent::setUp();

        $this->saveUploadedFileUsing(function (ProductVideoUpload $component, TemporaryUploadedFile $file): string {
            $input = $file->readStream();

            try {
                $path = app(ProductVideoStorage::class)->store(
                    $input,
                    $component->getDiskName(),
                    $component->getDirectory(),
                    (string) $component->evaluate($component->productName),
                    (string) $component->evaluate($component->filenameDescription),
                    app(ProductVideoStorage::class)->extension($file),
                    $component->getRecord() instanceof ProductVideo ? $component->getRecord()->path : null,
                );
            } finally {
                if (is_resource($input)) {
                    fclose($input);
                }
            }

            session()->put('product_video_uploads.'.hash('sha256', $path), $path);

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
                        $description = $this->evaluate($this->filenameDescription);
                        if (! is_string($description) || ProductImageStorage::descriptionSlug($description) === '') {
                            continue;
                        }

                        $storage = app(ProductVideoStorage::class);
                        $path = $storage->path($this->getDirectory(), (string) $this->evaluate($this->productName), $description);
                        if ($storage->hasConflict($this->getDiskName(), $path, $record instanceof ProductVideo ? $record->path : null)) {
                            $fail('Ya existe una imagen, video o archivo con ese nombre.');
                        }

                        continue;
                    }

                    if ($record instanceof ProductVideo && $file === $record->path) {
                        continue;
                    }

                    if (is_string($file)
                        && session()->get('product_video_uploads.'.hash('sha256', $file)) === $file
                        && Storage::disk($this->getDiskName())->exists($file)) {
                        continue;
                    }

                    $fail('El video seleccionado no es válido.');
                }
            },
        ];
    }
}
