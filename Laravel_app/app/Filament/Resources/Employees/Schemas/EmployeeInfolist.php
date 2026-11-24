<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Models\Employee;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('first_name'),
                TextEntry::make('last_name'),
                TextEntry::make('type_identity'),
                TextEntry::make('identity_number'),
                TextEntry::make('CUIL'),
                TextEntry::make('phone'),
                TextEntry::make('position')
                    ->placeholder('-'),
                TextEntry::make('address')
                    ->placeholder('-'),
                TextEntry::make('city')
                    ->placeholder('-'),
                TextEntry::make('state')
                    ->placeholder('-'),
                TextEntry::make('country')
                    ->placeholder('-'),
                TextEntry::make('postal_code')
                    ->placeholder('-'),
                TextEntry::make('birthdate')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('marital_status')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                Section::make('User Information')
                    ->columns(2)
                    ->columnSpanFull()
                    ->components([
                        TextEntry::make('user.id')
                            ->label('ID')
                            ->placeholder('-'),
                        TextEntry::make('user.user_name')
                            ->placeholder('-'),
                        TextEntry::make('user.email')
                            ->label('Email address')
                            ->placeholder('-'),
                        TextEntry::make('user.roles.name')
                            ->label('Roles')
                            ->badge()
                            ->placeholder('Sin rol'),
                        TextEntry::make('user.type')
                            ->placeholder('-'),
                        IconEntry::make('user.is_active')
                            ->boolean()
                            ->visible(fn (Employee $record): bool => $record->user !== null),
                        TextEntry::make('user.email_verified_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('user.created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('user.updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
