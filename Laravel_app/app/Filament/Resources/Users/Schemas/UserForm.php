<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Support\UserRoleAssignments;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                Select::make('role_id')
                    ->label('Rol')
                    ->options(fn (?User $record): array => app(UserRoleAssignments::class)->options(Filament::auth()->user(), $record))
                    ->searchable()
                    ->preload()
                    ->visible(fn (): bool => app(UserRoleAssignments::class)->allowedRoles(Filament::auth()->user()) !== [])
                    ->required(),
                Placeholder::make('current_role')
                    ->label('Rol')
                    ->content(fn (?User $record): string => $record?->getRoleNames()->join(', ') ?: 'Sin rol')
                    ->visible(fn (?User $record): bool => $record !== null && app(UserRoleAssignments::class)->allowedRoles(Filament::auth()->user()) === []),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->visibleOn('create')
                    ->required()
                    ->dehydrated(fn (?string $state, string $operation): bool => filled($state) && $operation === 'create')
                    ->same('passwordConfirmation')
                    ->validationAttribute('password'),
                TextInput::make('passwordConfirmation')
                    ->label('Confirm password')
                    ->password()
                    ->revealable()
                    ->visibleOn('create')
                    ->required()
                    ->dehydrated(false),
                DateTimePicker::make('email_verified_at')
                    ->disabled()
                    ->dehydrated(false),
                Section::make('Employee Profile')
                    ->relationship('employee')
                    ->description('Configuración del perfil.')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->components([
                        TextInput::make('first_name')
                            ->label('First name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label('Last name')
                            ->required()
                            ->maxLength(255),
                        Select::make('type_identity')
                            ->label('Type of Identity')
                            ->options([
                                'DNI' => 'DNI',
                                'Passport' => 'Passport',
                                'Other' => 'Other',
                            ])
                            ->required(),
                        TextInput::make('identity_number')
                            ->label('Identity Number')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('CUIL')
                            ->label('CUIL')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Phone')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('position')
                            ->label('Position')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('address')
                            ->label('Address')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('city')
                            ->label('City')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('state')
                            ->label('State')
                            ->maxLength(255)
                            ->required(),
                        TextInput::make('country')
                            ->label('Country')
                            ->maxLength(255)
                            ->required(),
                        TextInput::make('postal_code')
                            ->label('Postal Code')
                            ->maxLength(255)
                            ->required(),
                        DatePicker::make('birthdate')
                            ->label('Birthdate'),
                        Select::make('marital_status')
                            ->label('Marital Status')
                            ->options([
                                'Single' => 'Single',
                                'Married' => 'Married',
                                'Divorced' => 'Divorced',
                                'Widowed' => 'Widowed',
                            ])
                            ->required(),
                    ]),
            ]);
    }
}
