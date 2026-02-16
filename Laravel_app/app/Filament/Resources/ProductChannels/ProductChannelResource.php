<?php

namespace App\Filament\Resources\ProductChannels;

use App\Filament\Resources\ProductChannels\Pages\CreateProductChannel;
use App\Filament\Resources\ProductChannels\Pages\EditProductChannel;
use App\Filament\Resources\ProductChannels\Pages\ListProductChannels;
use App\Filament\Resources\ProductChannels\Pages\ViewProductChannel;
use App\Filament\Resources\ProductChannels\Schemas\ProductChannelForm;
use App\Filament\Resources\ProductChannels\Schemas\ProductChannelInfolist;
use App\Filament\Resources\ProductChannels\Tables\ProductChannelsTable;
use App\Models\ProductChannel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductChannelResource extends Resource
{
    protected static ?string $model = ProductChannel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'custom_title';

    public static function form(Schema $schema): Schema
    {
        return ProductChannelForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductChannelInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductChannelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductChannels::route('/'),
            'create' => CreateProductChannel::route('/create'),
            'view' => ViewProductChannel::route('/{record}'),
            'edit' => EditProductChannel::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
