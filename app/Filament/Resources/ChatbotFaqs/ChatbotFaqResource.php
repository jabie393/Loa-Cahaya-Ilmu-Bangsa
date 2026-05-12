<?php

namespace App\Filament\Resources\ChatbotFaqs;

use App\Filament\Resources\ChatbotFaqs\Pages\CreateChatbotFaq;
use App\Filament\Resources\ChatbotFaqs\Pages\EditChatbotFaq;
use App\Filament\Resources\ChatbotFaqs\Pages\ListChatbotFaqs;
use App\Filament\Resources\ChatbotFaqs\Schemas\ChatbotFaqForm;
use App\Filament\Resources\ChatbotFaqs\Tables\ChatbotFaqsTable;
use App\Models\ChatbotFaq;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ChatbotFaqResource extends Resource
{
    protected static ?string $model = ChatbotFaq::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CpuChip;

    protected static ?string $recordTitleAttribute = 'question';

    protected static string | UnitEnum | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ChatbotFaqForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChatbotFaqsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChatbotFaqs::route('/'),
            'create' => CreateChatbotFaq::route('/create'),
            'edit' => EditChatbotFaq::route('/{record}/edit'),
        ];
    }
}
