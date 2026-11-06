<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Support\UserRoleAssignments;
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
            ]);
    }
}
