<?php

namespace App\Filament\Resources\ProductChannels\Pages;

use App\Filament\Resources\ProductChannels\ProductChannelResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProductChannel extends ViewRecord
{
    protected static string $resource = ProductChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
