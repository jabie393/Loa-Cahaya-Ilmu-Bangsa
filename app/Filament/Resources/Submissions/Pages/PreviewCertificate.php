<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class PreviewCertificate extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SubmissionResource::class;

    protected string $view = 'filament.resources.submissions.pages.preview-certificate';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view($this->view)->layout('layouts.raw');    
    }
}
