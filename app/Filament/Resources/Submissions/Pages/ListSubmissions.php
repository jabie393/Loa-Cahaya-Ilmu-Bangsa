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
                            $defaultHost = null;
                            if (!empty($defaultUrl)) {
                                $defaultHost = parse_url($defaultUrl, PHP_URL_HOST);
                                if (empty($defaultHost)) {
                                    $defaultHost = str_replace(['https://', 'http://', '/'], '', $defaultUrl);
                                }
                            }

                            $dbUrls = Journal::query()
                                ->whereNotNull('ojs_base_url')
                                ->where('ojs_base_url', '<>', '')
                                ->distinct()
                                ->pluck('ojs_base_url')
                                ->toArray();

                            $options = [];

                            if (!empty($defaultUrl)) {
                                $options[$defaultUrl] = $defaultHost ?: 'Internal Website';
                            }

                            foreach ($dbUrls as $url) {
                                if (!empty($defaultUrl) && rtrim($url, '/') === rtrim($defaultUrl, '/')) {
                                    continue;
                                }

                                $host = parse_url($url, PHP_URL_HOST);
                                if (empty($host)) {
                                    $host = str_replace(['https://', 'http://', '/'], '', $url);
                                }
                                $options[$url] = $host ?: $url;
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
