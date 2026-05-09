<?php

namespace App\Filament\Resources\ChatbotFaqs\Pages;

use App\Filament\Resources\ChatbotFaqs\ChatbotFaqResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChatbotFaq extends EditRecord
{
    protected static string $resource = ChatbotFaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
