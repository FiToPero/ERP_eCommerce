<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
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

                ImageEntry::make('image_link')
                    ->label('Imagen principal')
                    ->getStateUsing(fn (Product $record): ?string => Product::resolveImageUrl($record->image_link))
                    ->imageHeight(220)
                    ->square()
                    ->columnSpanFull(),

                TextEntry::make('image_link')
                    ->label('URL imagen principal')
                    ->url(fn (?string $state) => Product::resolveImageUrl($state))
                    ->openUrlInNewTab()
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('additional_image_links')
                    ->label('Imágenes adicionales')
                    ->formatStateUsing(fn ($state) => blank($state) ? '-' : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                    ->columnSpanFull(),

                TextEntry::make('video_link')
                    ->label('Video del producto')
                    ->url(fn (?string $state) => $state)
                    ->openUrlInNewTab()
                    ->placeholder('-')
                    ->columnSpanFull(),

                Section::make('ProductWeb')
                    ->description('Datos sincronizados del producto para la web.')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->components([
                        IconEntry::make('productWeb.is_active')
                            ->label('Habilitado para Web')
                            ->boolean(),

                        TextEntry::make('productWeb.status')
                            ->label('Estado')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'active' => 'success',
                                'pending' => 'warning',
                                'disapproved' => 'danger',
                                'draft' => 'gray',
                                default => 'gray',
                            })
                            ->placeholder('-'),

                        TextEntry::make('productWeb.link')
                            ->label('Product link')
                            ->url(fn (?string $state) => $state)
                            ->openUrlInNewTab()
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('productWeb.product_type')
                            ->label('Product type')
                            ->placeholder('-'),

                        TextEntry::make('productWeb.sale_price')
                            ->label('Precio oferta')
                            ->placeholder('-'),

                        TextEntry::make('productWeb.item_group_id')
                            ->label('Item group ID')
                            ->placeholder('-'),

                        TextEntry::make('productWeb.sale_price_start')
                            ->label('Inicio oferta')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('productWeb.sale_price_end')
                            ->label('Fin oferta')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),

                Section::make('Google Merchant Center')
                    ->description('Datos sincronizados del producto para Google Shopping.')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->components([
                        IconEntry::make('productGoogle.is_active')
                            ->label('Habilitado para Google')
                            ->boolean(),

                        TextEntry::make('productGoogle.status')
                            ->label('Estado')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'active' => 'success',
                                'pending' => 'warning',
                                'disapproved' => 'danger',
                                'draft' => 'gray',
                                default => 'gray',
                            })
                            ->placeholder('-'),

                        TextEntry::make('productGoogle.google_id')
                            ->label('Google ID')
                            ->placeholder('-'),

                        TextEntry::make('productGoogle.last_synced_at')
                            ->label('Ultima sincronizacion')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('productGoogle.google_url')
                            ->label('Google URL')
                            ->url(fn (?string $state) => $state)
                            ->openUrlInNewTab()
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('productGoogle.title')
                            ->label('Titulo Google')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('productGoogle.description')
                            ->label('Descripcion Google')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('productGoogle.link')
                            ->label('Product link')
                            ->url(fn (?string $state) => $state)
                            ->openUrlInNewTab()
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('productGoogle.price')
                            ->label('Precio')
                            ->money('USD')
                            ->placeholder('-'),

                        TextEntry::make('productGoogle.currency')
                            ->label('Moneda')
                            ->placeholder('-'),

                        TextEntry::make('productGoogle.availability')
                            ->label('Disponibilidad')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make('productGoogle.google_product_category')
                            ->label('Google product category')
                            ->placeholder('-'),

                        TextEntry::make('productGoogle.product_type')
                            ->label('Product type')
                            ->placeholder('-'),

                        TextEntry::make('productGoogle.sale_price')
                            ->label('Precio oferta')
                            ->money('USD')
                            ->placeholder('-'),

                        TextEntry::make('productGoogle.item_group_id')
                            ->label('Item group ID')
                            ->placeholder('-'),

                        TextEntry::make('productGoogle.sale_price_start')
                            ->label('Inicio oferta')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('productGoogle.sale_price_end')
                            ->label('Fin oferta')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('productGoogle.sync_errors')
                            ->label('Sync errors')
                            ->formatStateUsing(fn ($state) => blank($state) ? '-' : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                            ->columnSpanFull(),

                    ]),

                Section::make('MercadoLibre')
                    ->description('Datos sincronizados del producto para MercadoLibre.')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->components([
                        IconEntry::make('productMercadolibre.is_active')
                            ->label('Habilitado para MercadoLibre')
                            ->boolean(),

                        TextEntry::make('productMercadolibre.status')
                            ->label('Estado')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'active' => 'success',
                                'pending' => 'warning',
                                'paused' => 'warning',
                                'error' => 'danger',
                                'draft' => 'gray',
                                default => 'gray',
                            })
                            ->placeholder('-'),

                        TextEntry::make('productMercadolibre.ml_id')
                            ->label('MercadoLibre ID')
                            ->placeholder('-'),

                        TextEntry::make('productMercadolibre.ml_url')
                            ->label('MercadoLibre URL')
                            ->url(fn (?string $state) => $state)
                            ->openUrlInNewTab()
                            ->placeholder('-'),

                        TextEntry::make('productMercadolibre.last_synced_at')
                            ->label('Ultima sincronizacion')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('productMercadolibre.title')
                            ->label('Titulo ML')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('productMercadolibre.category_id')
                            ->label('Category ID')
                            ->placeholder('-'),

                        TextEntry::make('productMercadolibre.price')
                            ->label('Precio')
                            ->placeholder('-'),

                        TextEntry::make('productMercadolibre.currency_id')
                            ->label('Moneda')
                            ->placeholder('-'),

                        TextEntry::make('productMercadolibre.available_quantity')
                            ->label('Stock disponible')
                            ->placeholder('-'),

                        TextEntry::make('productMercadolibre.buying_mode')
                            ->label('Buying mode')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make('productMercadolibre.listing_type_id')
                            ->label('Listing type ID')
                            ->placeholder('-'),

                        TextEntry::make('productMercadolibre.attributes')
                            ->label('Attributes')
                            ->formatStateUsing(fn ($state) => blank($state) ? '-' : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                            ->columnSpanFull(),

                        TextEntry::make('productMercadolibre.sale_terms')
                            ->label('Sale terms')
                            ->formatStateUsing(fn ($state) => blank($state) ? '-' : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                            ->columnSpanFull(),

                        TextEntry::make('productMercadolibre.shipping')
                            ->label('Shipping')
                            ->formatStateUsing(fn ($state) => blank($state) ? '-' : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                            ->columnSpanFull(),

                        TextEntry::make('productMercadolibre.warranty')
                            ->label('Garantia')
                            ->placeholder('-'),

                        TextEntry::make('productMercadolibre.variations')
                            ->label('Variations')
                            ->formatStateUsing(fn ($state) => blank($state) ? '-' : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                            ->columnSpanFull(),

                        TextEntry::make('productMercadolibre.sync_errors')
                            ->label('Sync errors')
                            ->formatStateUsing(fn ($state) => blank($state) ? '-' : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                            ->columnSpanFull(),

                    ]),

                Section::make('ProductMeta')
                    ->description('Datos sincronizados del producto para Meta.')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->components([
                        IconEntry::make('productMeta.is_active')
                            ->label('Habilitado para Meta')
                            ->boolean(),

                        TextEntry::make('productMeta.status')
                            ->label('Estado')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'active' => 'success',
                                'pending' => 'warning',
                                'rejected' => 'danger',
                                'draft' => 'gray',
                                default => 'gray',
                            })
                            ->placeholder('-'),

                        TextEntry::make('productMeta.meta_id')
                            ->label('Meta ID')
                            ->placeholder('-'),

                        TextEntry::make('productMeta.permalink')
                            ->label('Permalink')
                            ->url(fn (?string $state) => $state)
                            ->openUrlInNewTab()
                            ->placeholder('-'),

                        TextEntry::make('productMeta.last_synced_at')
                            ->label('Ultima sincronizacion')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('productMeta.title')
                            ->label('Titulo Meta')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('productMeta.description')
                            ->label('Descripcion Meta')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('productMeta.availability')
                            ->label('Disponibilidad')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make('productMeta.condition')
                            ->label('Condición')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make('productMeta.price')
                            ->label('Precio')
                            ->placeholder('-'),

                        TextEntry::make('productMeta.link')
                            ->label('Product link')
                            ->url(fn (?string $state) => $state)
                            ->openUrlInNewTab()
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('productMeta.item_group_id')
                            ->label('Item group ID')
                            ->placeholder('-'),

                        TextEntry::make('productMeta.color')
                            ->label('Color')
                            ->placeholder('-'),

                        TextEntry::make('productMeta.size')
                            ->label('Tamaño')
                            ->placeholder('-'),

                        TextEntry::make('productMeta.google_product_category')
                            ->label('Google product category')
                            ->placeholder('-'),

                        TextEntry::make('productMeta.product_type')
                            ->label('Product type')
                            ->placeholder('-'),

                        TextEntry::make('productMeta.sale_price')
                            ->label('Precio oferta')
                            ->placeholder('-'),

                        TextEntry::make('productMeta.sale_price_start')
                            ->label('Inicio oferta')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('productMeta.sale_price_end')
                            ->label('Fin oferta')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('productMeta.sync_errors')
                            ->label('Sync errors')
                            ->formatStateUsing(fn ($state) => blank($state) ? '-' : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                            ->columnSpanFull(),
                    ]),

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
