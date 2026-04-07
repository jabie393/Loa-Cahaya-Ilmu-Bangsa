<?php

namespace App\Filament\Pages\Auth;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
            TextInput::make('name')
                ->label(__('Name'))
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
            ]);
    }
}
