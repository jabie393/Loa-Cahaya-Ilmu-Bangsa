<?php

namespace App\Filament\Resources\Journals\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;

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
                TextInput::make('image')
                    ->label('Image')
                    ->required(),
                TextInput::make('link')
                    ->label('Link')
                    ->required()
                    ->placeholder('https://...'),
                TextInput::make('template_link')
                    ->label('Template Link')
                    ->url()
                    ->placeholder('https://...'),
            ]);
    }
}
