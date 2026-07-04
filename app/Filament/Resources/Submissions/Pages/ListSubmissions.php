<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

use Filament\Forms\Components\Select;
use App\Models\Journal;

class ListSubmissions extends ListRecords
{
    protected static string $resource = SubmissionResource::class;

    protected string $view = 'filament.pages.list-submissions';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Buat Pengajuan Baru')
                ->icon('heroicon-o-plus')
                ->form([
                    Select::make('ojs_base_url')
                        ->label('Pilih Website Jurnal')
                        ->options(function () {
                            $defaultUrl = config('ojs.base_url');
                            $dbUrls = Journal::query()
                                ->whereNotNull('ojs_base_url')
                                ->where('ojs_base_url', '<>', '')
                                ->distinct()
                                ->pluck('ojs_base_url')
                                ->toArray();

                            $options = [];

                            if (!empty($defaultUrl)) {
                                $options[$defaultUrl] = 'a. Jurnal Nasional Non Sinta';
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

                            return $options;
                        })
                        ->required(),
                ])
                ->action(function (array $data) {
                    return redirect()->route('filament.admin.resources.submissions.create', [
                        'ojs_base_url' => $data['ojs_base_url']
                    ]);
                }),
        ];
    }
}
