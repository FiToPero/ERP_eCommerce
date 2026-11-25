<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Filament\Resources\Users\Schemas\UserForm;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    ->mutateRelationshipDataBeforeFillUsing(function (array $data, Section $component): array {
                        $data['role_id'] = $component->getCachedExistingRecord()?->roles()->value('roles.id');

                        return $data;
                    })
                    ->components(UserForm::userFields()),
            ]);
    }
}
