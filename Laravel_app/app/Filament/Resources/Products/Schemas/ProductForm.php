<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, callable $set) =>
                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                    ),

                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('short_description')
                    ->label('Descripción corta')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Descripción')
                    ->rows(4)
                    ->columnSpanFull(),

                TextInput::make('brand')
                    ->label('Marca')
                    ->maxLength(255),

                TextInput::make('barcode')
                    ->label('Código de barras (GTIN/EAN/UPC)')
                    ->maxLength(255),

                TextInput::make('mpn')
                    ->label('MPN (código fabricante)')
                    ->maxLength(255),

                Select::make('condition')
                    ->label('Condición')
                    ->options([
                        'new'          => 'Nuevo',
                        'refurbished'  => 'Reacondicionado',
                        'used'         => 'Usado',
                    ])
                    ->default('new')
                    ->required(),

                Toggle::make('identifier_exists')
                    ->label('¿Tiene identificadores válidos? (brand/GTIN/MPN)')
                    ->default(true)
                    ->columnSpanFull(),

                DateTimePicker::make('availability_date')
                    ->label('Fecha de disponibilidad')
                    ->helperText('Requerido si el producto es pre-order.'),
            ]);
    }
}
