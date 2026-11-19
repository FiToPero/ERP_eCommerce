<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\Customer;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('customer_name')
                    ->required(),
                Select::make('type')
                    ->default('personal')
                    ->options([
                        'personal' => 'Personal',
                        'business' => 'Business',
                    ]),
                Select::make('web')
                    ->required()
                    ->options([
                        env('FRONTEND_URL_WEB_01') => env('FRONTEND_URL_WEB_01'),
                        env('FRONTEND_URL_WEB_02') => env('FRONTEND_URL_WEB_02'),
                    ])
                    ->helperText('Origen de la tienda, por ejemplo https://tienda.example.'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
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
                    ->visibleOn('edit'),
                Toggle::make('profile_enabled')
                    ->label('Agregar perfil')
                    ->live()
                    ->dehydrated(false)
                    ->visible(fn (?Customer $record): bool => ! $record?->profile()->exists())
                    ->columnSpanFull(),
                Section::make('Profile')
                    ->relationship('profile')
                    ->visible(fn (Get $get, ?Customer $record): bool => (bool) $get('profile_enabled') || (bool) $record?->profile()->exists())
                    ->description('Configuración del perfil.')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->components([
                        TextInput::make('first_name')
                            ->label('First name')
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label('Last name')
                            ->maxLength(255),
                        TextInput::make('company_name')
                            ->label('Company name')
                            ->maxLength(255),
                        TextInput::make('cuit')
                            ->label('CUIT')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Phone')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('birthdate')
                            ->label('Birthdate'),
                        Textarea::make('bio')
                            ->label('Bio'),
                        TextInput::make('website')
                            ->label('Website')
                            ->maxLength(255),
                        TextInput::make('instagram')
                            ->label('Instagram')
                            ->maxLength(255),
                        TextInput::make('twitter')
                            ->label('Twitter')
                            ->maxLength(255),
                        TextInput::make('linkedin')
                            ->label('LinkedIn')
                            ->maxLength(255),
                        TextInput::make('github')
                            ->label('GitHub')
                            ->maxLength(255),
                        TextInput::make('facebook')
                            ->label('Facebook')
                            ->maxLength(255),
                    ]),
            ]);
    }
}
