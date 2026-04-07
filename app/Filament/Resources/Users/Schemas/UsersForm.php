<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UsersForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique('users', 'email', ignoreRecord: true),

                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn($operation) => $operation === 'create') // password is only required on create, not edit.
                    ->dehydrated(fn($state) => filled($state)) // prevents sending empty values to the database (so it doesn’t overwrite existing password).
                    ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null) // if user types something, hash it; otherwise leave as null.
                    ->label(__('Password')),

                Select::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name', fn($query) => $query->where('name', '!=', 'super_admin'))
                    ->preload()
                    ->required()
                    ->live(),

            ]
        );
    }
}
