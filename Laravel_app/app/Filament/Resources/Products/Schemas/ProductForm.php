<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
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

                TextInput::make('image_link')
                    ->label('Imagen principal')
                    ->live(onBlur: true)
                    ->url()
                    ->helperText('Acepta una URL publica o una ruta de archivo en el disco publico.')
                    ->maxLength(2000)
                    ->columnSpanFull(),

                Section::make('Vista previa de imagen')
                    ->visible(fn (Get $get): bool => filled($get('image_link')))
                    ->columnSpanFull()
                    ->components([
                        Html::make(function (Get $get): HtmlString {
                            $imageUrl = Product::resolveImageUrl($get('image_link'));
                            $imageAlt = e($get('name') ?: 'Imagen principal');

                            if (blank($imageUrl)) {
                                return new HtmlString('');
                            }

                            $escapedUrl = e($imageUrl);

                            return new HtmlString(<<<HTML
<div class="flex flex-col gap-3">
    <img src="{$escapedUrl}" alt="{$imageAlt}" class="max-h-80 w-auto rounded-xl border border-gray-200 object-contain" loading="lazy">
    <a href="{$escapedUrl}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-primary-600 hover:underline">Abrir imagen en una nueva pestaña</a>
</div>
HTML);
                        }),
                    ]),

                Textarea::make('additional_image_links')
                    ->label('Imágenes adicionales')
                    ->helperText('JSON array de URLs adicionales del producto.')
                    ->rows(4)
                    ->formatStateUsing(fn ($state) => blank($state) ? null : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                    ->dehydrateStateUsing(fn ($state) => blank($state) ? null : json_decode($state, true))
                    ->rule('json')
                    ->columnSpanFull(),

                TextInput::make('video_link')
                    ->label('Video del producto')
                    ->url()
                    ->maxLength(2000)
                    ->columnSpanFull(),

                Section::make('ProductWeb')
                    ->relationship('productWeb')
                    ->description('Configuracion y datos sincronizados del producto para la web.')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->components([
                        Toggle::make('is_active')
                            ->label('Habilitado para Web')
                            ->default(false)
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'disapproved' => 'Disapproved',
                            ])
                            ->default('draft')
                            ->required(),

                        TextInput::make('link')
                            ->label('Product link')
                            ->url()
                            ->maxLength(2000)
                            ->columnSpanFull(),

                        TextInput::make('product_type')
                            ->label('Product type')
                            ->maxLength(750),

                        TextInput::make('sale_price')
                            ->label('Precio oferta')
                            ->numeric()
                            ->prefix('$'),

                        TextInput::make('item_group_id')
                            ->label('Item group ID')
                            ->maxLength(255),

                        DateTimePicker::make('sale_price_start')
                            ->label('Inicio oferta'),

                        DateTimePicker::make('sale_price_end')
                            ->label('Fin oferta'),
                    ]),

                Section::make('Google Merchant Center')
                    ->relationship('productGoogle')
                    ->description('Configuracion y datos sincronizados del producto para Google Shopping.')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->components([
                        Toggle::make('is_active')
                            ->label('Habilitado para Google')
                            ->default(false)
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'disapproved' => 'Disapproved',
                            ])
                            ->default('draft')
                            ->required(),

                        DateTimePicker::make('last_synced_at')
                            ->label('Ultima sincronizacion'),

                        TextInput::make('google_id')
                            ->label('Google ID')
                            ->maxLength(255),

                        TextInput::make('google_url')
                            ->label('Google URL')
                            ->url()
                            ->maxLength(2000),

                        TextInput::make('title')
                            ->label('Titulo Google')
                            ->maxLength(150)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Descripcion Google')
                            ->rows(4)
                            ->columnSpanFull(),

                        TextInput::make('link')
                            ->label('Product link')
                            ->url()
                            ->maxLength(2000)
                            ->columnSpanFull(),

                        TextInput::make('price')
                            ->label('Precio')
                            ->numeric()
                            ->prefix('$'),

                        TextInput::make('currency')
                            ->label('Moneda')
                            ->default('USD')
                            ->minLength(3)
                            ->maxLength(3),

                        Select::make('availability')
                            ->label('Disponibilidad')
                            ->options([
                                'in_stock' => 'In stock',
                                'out_of_stock' => 'Out of stock',
                                'preorder' => 'Preorder',
                                'backorder' => 'Backorder',
                            ])
                            ->default('in_stock')
                            ->required(),

                        TextInput::make('google_product_category')
                            ->label('Google product category')
                            ->maxLength(255),

                        TextInput::make('product_type')
                            ->label('Product type')
                            ->maxLength(750),

                        TextInput::make('sale_price')
                            ->label('Precio oferta')
                            ->numeric()
                            ->prefix('$'),

                        TextInput::make('item_group_id')
                            ->label('Item group ID')
                            ->maxLength(255),

                        DateTimePicker::make('sale_price_start')
                            ->label('Inicio oferta'),

                        DateTimePicker::make('sale_price_end')
                            ->label('Fin oferta'),

                        Textarea::make('sync_errors')
                            ->label('Sync errors')
                            ->helperText('JSON array con errores devueltos por Google.')
                            ->rows(4)
                            ->formatStateUsing(fn ($state) => blank($state) ? null : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                            ->dehydrateStateUsing(fn ($state) => blank($state) ? null : json_decode($state, true))
                            ->rule('json')
                            ->columnSpanFull(),

                    ]),

                Section::make('MercadoLibre')
                    ->relationship('productMercadolibre')
                    ->description('Configuracion y datos sincronizados del producto para MercadoLibre.')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->components([
                        Toggle::make('is_active')
                            ->label('Habilitado para MercadoLibre')
                            ->default(false)
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'paused' => 'Paused',
                                'error' => 'Error',
                            ])
                            ->default('draft')
                            ->required(),

                        TextInput::make('ml_id')
                            ->label('MercadoLibre ID')
                            ->maxLength(255),

                        TextInput::make('ml_url')
                            ->label('MercadoLibre URL')
                            ->url()
                            ->maxLength(2000),

                        DateTimePicker::make('last_synced_at')
                            ->label('Ultima sincronizacion'),

                        TextInput::make('title')
                            ->label('Titulo ML')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('category_id')
                            ->label('Category ID')
                            ->maxLength(255),

                        TextInput::make('price')
                            ->label('Precio')
                            ->numeric(),

                        TextInput::make('currency_id')
                            ->label('Moneda')
                            ->default('ARS')
                            ->minLength(3)
                            ->maxLength(3),

                        TextInput::make('available_quantity')
                            ->label('Stock disponible')
                            ->numeric(),

                        Select::make('buying_mode')
                            ->label('Buying mode')
                            ->options([
                                'buy_it_now' => 'Buy it now',
                            ])
                            ->default('buy_it_now')
                            ->required(),

                        TextInput::make('listing_type_id')
                            ->label('Listing type ID')
                            ->maxLength(255),

                        Textarea::make('attributes')
                            ->label('Attributes')
                            ->helperText('JSON array de atributos dinámicos.')
                            ->rows(4)
                            ->formatStateUsing(fn ($state) => blank($state) ? null : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                            ->dehydrateStateUsing(fn ($state) => blank($state) ? null : json_decode($state, true))
                            ->rule('json')
                            ->columnSpanFull(),

                        Textarea::make('sale_terms')
                            ->label('Sale terms')
                            ->helperText('JSON array de términos de venta.')
                            ->rows(4)
                            ->formatStateUsing(fn ($state) => blank($state) ? null : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                            ->dehydrateStateUsing(fn ($state) => blank($state) ? null : json_decode($state, true))
                            ->rule('json')
                            ->columnSpanFull(),

                        Textarea::make('shipping')
                            ->label('Shipping')
                            ->helperText('JSON object de configuración de envío.')
                            ->rows(4)
                            ->formatStateUsing(fn ($state) => blank($state) ? null : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                            ->dehydrateStateUsing(fn ($state) => blank($state) ? null : json_decode($state, true))
                            ->rule('json')
                            ->columnSpanFull(),

                        TextInput::make('warranty')
                            ->label('Garantia')
                            ->maxLength(255),

                        Textarea::make('variations')
                            ->label('Variations')
                            ->helperText('JSON array de variantes.')
                            ->rows(4)
                            ->formatStateUsing(fn ($state) => blank($state) ? null : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                            ->dehydrateStateUsing(fn ($state) => blank($state) ? null : json_decode($state, true))
                            ->rule('json')
                            ->columnSpanFull(),

                        Textarea::make('sync_errors')
                            ->label('Sync errors')
                            ->helperText('JSON array con errores devueltos por MercadoLibre.')
                            ->rows(4)
                            ->formatStateUsing(fn ($state) => blank($state) ? null : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                            ->dehydrateStateUsing(fn ($state) => blank($state) ? null : json_decode($state, true))
                            ->rule('json')
                            ->columnSpanFull(),

                    ]),

                Section::make('ProductMeta')
                    ->relationship('productMeta')
                    ->description('Configuracion y datos sincronizados del producto para Meta.')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->components([
                        Toggle::make('is_active')
                            ->label('Habilitado para Meta')
                            ->default(false)
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'rejected' => 'Rejected',
                            ])
                            ->default('draft')
                            ->required(),

                        TextInput::make('meta_id')
                            ->label('Meta ID')
                            ->maxLength(255),

                        TextInput::make('permalink')
                            ->label('Permalink')
                            ->url()
                            ->maxLength(2000),

                        DateTimePicker::make('last_synced_at')
                            ->label('Ultima sincronizacion'),

                        TextInput::make('title')
                            ->label('Titulo Meta')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Descripcion Meta')
                            ->rows(4)
                            ->columnSpanFull(),

                        Select::make('availability')
                            ->label('Disponibilidad')
                            ->options([
                                'in stock' => 'In stock',
                                'out of stock' => 'Out of stock',
                                'preorder' => 'Preorder',
                                'available for order' => 'Available for order',
                            ]),

                        Select::make('condition')
                            ->label('Condición')
                            ->options([
                                'new' => 'Nuevo',
                                'used' => 'Usado',
                                'refurbished' => 'Reacondicionado',
                            ])
                            ->default('new'),

                        TextInput::make('price')
                            ->label('Precio')
                            ->maxLength(255),

                        TextInput::make('link')
                            ->label('Product link')
                            ->url()
                            ->maxLength(2000)
                            ->columnSpanFull(),

                        TextInput::make('item_group_id')
                            ->label('Item group ID')
                            ->maxLength(255),

                        TextInput::make('color')
                            ->label('Color')
                            ->maxLength(255),

                        TextInput::make('size')
                            ->label('Tamaño')
                            ->maxLength(255),

                        TextInput::make('google_product_category')
                            ->label('Google product category')
                            ->maxLength(255),

                        TextInput::make('product_type')
                            ->label('Product type')
                            ->maxLength(255),

                        TextInput::make('sale_price')
                            ->label('Precio oferta')
                            ->numeric(),

                        DateTimePicker::make('sale_price_start')
                            ->label('Inicio oferta'),

                        DateTimePicker::make('sale_price_end')
                            ->label('Fin oferta'),

                        Textarea::make('sync_errors')
                            ->label('Sync errors')
                            ->helperText('JSON array con errores devueltos por Meta.')
                            ->rows(4)
                            ->formatStateUsing(fn ($state) => blank($state) ? null : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                            ->dehydrateStateUsing(fn ($state) => blank($state) ? null : json_decode($state, true))
                            ->rule('json')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
