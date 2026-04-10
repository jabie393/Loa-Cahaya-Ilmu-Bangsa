<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Str;

class PreviewLoa extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SubmissionResource::class;

    protected string $view = 'filament.resources.submissions.pages.preview-loa';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTemplateView(): string
    {
        $journal = $this->record->journal;
        if (!$journal) {
            return 'filament.loa_pdf.default'; // Fallback if needed
        }

        // Mapping slug to directory name
        // Slugs: argopuro, jayabama, panorama, medicnutricia, hibrida, trigonometri, musytari, causa, triwikrama, krepa, sindoro, liberosis, kohesi, tashdiq
        
        $slug = $journal->slug;
        
        // Match directory names like LOA_Jayabama, LOA_Argopuro
        // Special mapping for those with spaces or different names
        $mapping = [
            'medicnutricia' => 'Medic Nutricia',
        ];

        $folderName = $mapping[$slug] ?? Str::studly($slug);
        
        return "filament.loa_pdf.LOA_{$folderName}.LOA_{$folderName}";
    }

    public function render(): \Illuminate\Contracts\View\View
    {
    return view($this->view)->layout('layouts.raw');    
    }
}
