<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->placeholder('Sin rol'),
                TextEntry::make('email_verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                Section::make('Employee Profile')
                    ->columns(2)
                    ->columnSpanFull()
                    ->components([
                        TextEntry::make('employee.id')
                            ->label('ID')
                            ->placeholder('-'),
                        TextEntry::make('employee.first_name')
                            ->placeholder('-'),
                        TextEntry::make('employee.last_name')
                            ->placeholder('-'),
                        TextEntry::make('employee.type_identity')
                            ->placeholder('-'),
                        TextEntry::make('employee.identity_number')
                            ->placeholder('-'),
                        TextEntry::make('employee.CUIL')
                            ->label('CUIL')
                            ->placeholder('-'),
                        TextEntry::make('employee.phone')
                            ->placeholder('-'),
                        TextEntry::make('employee.position')
                            ->placeholder('-'),
                        TextEntry::make('employee.address')
                            ->placeholder('-'),
                        TextEntry::make('employee.city')
                            ->placeholder('-'),
                        TextEntry::make('employee.state')
                            ->placeholder('-'),
                        TextEntry::make('employee.country')
                            ->placeholder('-'),
                        TextEntry::make('employee.postal_code')
                            ->placeholder('-'),
                        TextEntry::make('employee.birthdate')
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('employee.marital_status')
                            ->placeholder('-'),
                        TextEntry::make('employee.created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('employee.updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('employee.deleted_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
