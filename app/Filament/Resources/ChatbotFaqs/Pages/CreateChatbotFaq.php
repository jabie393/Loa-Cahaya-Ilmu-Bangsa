<?php

namespace App\Filament\Resources\ChatbotFaqs\Pages;

use App\Filament\Resources\ChatbotFaqs\ChatbotFaqResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChatbotFaq extends CreateRecord
{
    protected static string $resource = ChatbotFaqResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
