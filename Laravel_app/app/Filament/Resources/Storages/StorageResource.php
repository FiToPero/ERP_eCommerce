<?php

namespace App\Filament\Resources\Storages;

use App\Filament\Resources\Storages\Pages\CreateStorage;
use App\Filament\Resources\Storages\Pages\EditStorage;
use App\Filament\Resources\Storages\Pages\ListStorages;
use App\Filament\Resources\Storages\Pages\ViewStorage;
use App\Filament\Resources\Storages\Schemas\StorageForm;
use App\Filament\Resources\Storages\Schemas\StorageInfolist;
use App\Filament\Resources\Storages\Tables\StoragesTable;
use App\Models\Storage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StorageResource extends Resource
{
    protected static ?string $model = Storage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return StorageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StorageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StoragesTable::configure($table);
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
            'index' => ListStorages::route('/'),
            'create' => CreateStorage::route('/create'),
            'view' => ViewStorage::route('/{record}'),
            'edit' => EditStorage::route('/{record}/edit'),
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
