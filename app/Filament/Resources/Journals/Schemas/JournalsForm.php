<?php

namespace App\Filament\Resources\Journals\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class JournalsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Jurnal')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
            ]);
    }
}
