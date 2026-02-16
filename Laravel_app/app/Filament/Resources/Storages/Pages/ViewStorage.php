<?php

namespace App\Filament\Resources\Storages\Pages;

use App\Filament\Resources\Storages\StorageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStorage extends ViewRecord
{
    protected static string $resource = StorageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
