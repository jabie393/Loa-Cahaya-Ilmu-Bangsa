<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use App\Models\Journal as JournalModel;

class Journal extends Page
{
    protected string $view = 'filament.pages.journal';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::BookOpen;
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = '1. Unduh Template';
    protected static ?string $title = 'Unduh Template';

    // Livewire property for filtering website OJS
    public ?string $ojs_base_url = 'default_env';

    public function getJurnals(): array
    {
        $query = JournalModel::query();
        
        if ($this->ojs_base_url === 'default_env') {
            $query->where(function ($q) {
                $q->whereNull('ojs_base_url')
                  ->orWhere('ojs_base_url', '');
            });
        } elseif ($this->ojs_base_url === 'all') {
            // No filter
        } elseif (!empty($this->ojs_base_url)) {
            $query->where('ojs_base_url', $this->ojs_base_url);
        }

        return $query->get()->toArray();
    }

    public function getOjsWebsites(): array
    {
        $defaultUrl = config('ojs.base_url');
        $defaultHost = null;
        if (!empty($defaultUrl)) {
            $defaultHost = parse_url($defaultUrl, PHP_URL_HOST);
            if (empty($defaultHost)) {
                $defaultHost = str_replace(['https://', 'http://', '/'], '', $defaultUrl);
            }
        }

        $dbUrls = JournalModel::query()
            ->whereNotNull('ojs_base_url')
            ->where('ojs_base_url', '<>', '')
            ->distinct()
            ->pluck('ojs_base_url')
            ->toArray();

        $options = [];
        if (!empty($defaultUrl)) {
            $options['default_env'] = 'a. Jurnal Nasional Non Sinta';
        }

        foreach ($dbUrls as $url) {
            if (!empty($defaultUrl) && rtrim($url, '/') === rtrim($defaultUrl, '/')) {
                continue;
            }

            $host = parse_url($url, PHP_URL_HOST);
            if (empty($host)) {
                $host = str_replace(['https://', 'http://', '/'], '', $url);
            }

            if ($host === 'ijefijournal.com') {
                $options[$url] = 'b. IJEFI Non-Scopus Indexed Journal of Economics and Management';
            } elseif ($host === 'pjlsedu.com') {
                $options[$url] = 'c. PJLSS Non-Scopus Indexed Multidisciplinary Journal';
            } else {
                $options[$url] = $host ?: $url;
            }
        }

        $options['all'] = 'Tampilkan Semua';

        return $options;
    }
}
