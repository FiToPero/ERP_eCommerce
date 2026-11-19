<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\Customer;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customer_name'),
                TextEntry::make('type'),
                TextEntry::make('web')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('email_verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Customer $record): bool => $record->trashed()),
                Section::make('Profile')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('profile.first_name')->label('First name')->placeholder('-'),
                        TextEntry::make('profile.last_name')->label('Last name')->placeholder('-'),
                        TextEntry::make('profile.company_name')->label('Company name')->placeholder('-'),
                        TextEntry::make('profile.cuit')->label('CUIT')->placeholder('-'),
                        TextEntry::make('profile.phone')->label('Phone')->placeholder('-'),
                        TextEntry::make('profile.birthdate')->label('Birthdate')->date()->placeholder('-'),
                        TextEntry::make('profile.bio')->label('Bio')->placeholder('-'),
                        TextEntry::make('profile.website')->label('Website')->placeholder('-'),
                        TextEntry::make('profile.instagram')->label('Instagram')->placeholder('-'),
                        TextEntry::make('profile.twitter')->label('Twitter')->placeholder('-'),
                        TextEntry::make('profile.linkedin')->label('LinkedIn')->placeholder('-'),
                        TextEntry::make('profile.github')->label('GitHub')->placeholder('-'),
                        TextEntry::make('profile.facebook')->label('Facebook')->placeholder('-'),
                    ])
                    ->visible(fn (Customer $record): bool => $record->profile !== null),
                TextEntry::make('profile_missing')
                    ->label('Profile')
                    ->state('Este cliente no tiene perfil.')
                    ->visible(fn (Customer $record): bool => $record->profile === null),
            ]);
    }
}
