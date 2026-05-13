<?php

namespace App\Filament\Resources\ChatbotFaqs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ChatbotFaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('question')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('answer')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('keywords')
                    ->columnSpanFull(),
                TextInput::make('category')
                    ->columnSpanFull(),
                Grid::make(2)
                    ->schema([
                        Toggle::make('is_active')
                            ->required(),
                        Toggle::make('is_popular')
                            ->required(),
                    ]),
            ]);
    }
}
