<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('category.name')
                    ->label('Categoría'),

                TextEntry::make('name')
                    ->label('Nombre'),

                TextEntry::make('sku')
                    ->label('SKU'),

                TextEntry::make('slug'),

                TextEntry::make('short_description')
                    ->label('Descripción corta')
                    ->columnSpanFull(),

                TextEntry::make('description')
                    ->label('Descripción')
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('brand')
                    ->label('Marca')
                    ->placeholder('-'),

                TextEntry::make('barcode')
                    ->label('Código de barras')
                    ->placeholder('-'),

                TextEntry::make('mpn')
                    ->label('MPN')
                    ->placeholder('-'),

                TextEntry::make('condition')
                    ->label('Condición')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new'         => 'success',
                        'refurbished' => 'warning',
                        'used'        => 'danger',
                        default       => 'gray',
                    }),

                IconEntry::make('identifier_exists')
                    ->label('Identificadores válidos')
                    ->boolean(),

                TextEntry::make('availability_date')
                    ->label('Fecha de disponibilidad')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('deleted_at')
                    ->label('Eliminado')
                    ->dateTime()
                    ->visible(fn (Product $record): bool => $record->trashed()),
            ]);
    }
}
