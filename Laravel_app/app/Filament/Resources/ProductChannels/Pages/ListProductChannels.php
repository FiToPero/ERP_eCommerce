<?php

namespace App\Filament\Resources\ProductChannels\Pages;

use App\Filament\Resources\ProductChannels\ProductChannelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductChannels extends ListRecords
{
    protected static string $resource = ProductChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
