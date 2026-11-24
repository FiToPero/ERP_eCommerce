<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Models\User;
use App\Support\UserRoleAssignments;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('first_name')
                    ->required(),
                TextInput::make('last_name')
                    ->required(),
                TextInput::make('type_identity')
                    ->required()
                    ->default('DNI'),
                TextInput::make('identity_number')
                    ->required(),
                TextInput::make('CUIL')
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('position'),
                TextInput::make('address'),
                TextInput::make('city'),
                TextInput::make('state'),
                TextInput::make('country'),
                TextInput::make('postal_code'),
                DatePicker::make('birthdate'),
                TextInput::make('marital_status'),
                Section::make('User Employee')
                    ->relationship('user')
                    ->description('Configuración del user.')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->components([
                        TextInput::make('user_name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->unique(ignoreRecord: true)
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
                    ]),
            ]);
    }
}
