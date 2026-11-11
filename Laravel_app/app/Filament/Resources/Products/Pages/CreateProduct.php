<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;
use Livewire\Attributes\Locked;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    #[Locked]
    public ?string $imageDirectory = null;

    protected function beforeValidate(): void
    {
        $this->imageDirectory ??= Product::makeImageDirectory((string) ($this->data['name'] ?? ''));
        $this->data['image_directory'] = $this->imageDirectory;
    }

    protected function afterCreate(): void
    {
        $this->imageDirectory = null;
    }
}
