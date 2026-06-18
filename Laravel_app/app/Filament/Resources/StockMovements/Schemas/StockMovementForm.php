<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use App\Models\StockMovement;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('storage_id')
                    ->required()
                    ->numeric(),
                Select::make('direction')
                    ->options(StockMovement::directionOptions())
                    ->required(),
                Select::make('type')
                    ->options(StockMovement::typeOptions())
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
