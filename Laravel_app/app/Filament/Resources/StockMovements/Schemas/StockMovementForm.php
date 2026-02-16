<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_id')
                    ->required()
                    ->numeric(),
                TextInput::make('storage_id')
                    ->required()
                    ->numeric(),
                TextInput::make('direction')
                    ->required(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                TextInput::make('unit_cost')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('reference_type'),
                TextInput::make('reference_id'),
                DateTimePicker::make('moved_at')
                    ->required(),
                Textarea::make('note')
                    ->columnSpanFull(),
                TextInput::make('metadata'),
            ]);
    }
}
