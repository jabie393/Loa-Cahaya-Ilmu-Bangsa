<?php

namespace App\Filament\Resources\ChatbotFaqs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ChatbotFaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('question')
                    ->required(),
                Textarea::make('answer')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('keywords')
                    ->columnSpanFull(),
                TextInput::make('category'),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_popular')
                    ->required(),
            ]);
    }
}
