<?php

namespace App\Filament\Resources\ProductChannels\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductChannelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_id')
                    ->required()
                    ->numeric(),
                TextInput::make('channel_id')
                    ->required()
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
                DateTimePicker::make('published_at'),
                TextInput::make('custom_title'),
                Textarea::make('custom_description')
                    ->columnSpanFull(),
                TextInput::make('custom_price')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('metadata'),
                DateTimePicker::make('last_synced_at'),
                TextInput::make('external_id'),
                TextInput::make('external_url')
                    ->url(),
            ]);
    }
}
