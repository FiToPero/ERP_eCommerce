<?php

namespace App\Filament\Resources\ProductChannels\Schemas;

use App\Models\ProductChannel;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductChannelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('product_id')
                    ->numeric(),
                TextEntry::make('channel_id')
                    ->numeric(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('published_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('custom_title')
                    ->placeholder('-'),
                TextEntry::make('custom_description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('custom_price')
                    ->money()
                    ->placeholder('-'),
                TextEntry::make('last_synced_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('external_id')
                    ->placeholder('-'),
                TextEntry::make('external_url')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (ProductChannel $record): bool => $record->trashed()),
            ]);
    }
}
