<?php

namespace App\Filament\Resources\Journals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;

class JournalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Jurnal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('image')
                    ->label('Image')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ojs_base_url')
                    ->label('Website OJS')
                    ->placeholder('Default')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) {
                            return 'Default';
                        }
                        $host = parse_url($state, PHP_URL_HOST);
                        if (empty($host)) {
                            $host = str_replace(['https://', 'http://', '/'], '', $state);
                        }
                        return $host;
                    }),
                TextColumn::make('link')
                    ->label('Link Jurnal')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn() => 'Buka Link')
                    ->url(fn($record) => $record->link, true),
                TextColumn::make('template_link')
                    ->label('Template')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn($state) => $state ? 'Download' : '-')
                    ->url(fn($record) => $record->template_link, true),

            ])
            ->groups([
                Group::make('ojs_base_url')
                    ->label('Website OJS')
                    ->collapsible()
                    ->getTitleFromRecordUsing(function ($record) {
                        $url = $record->ojs_base_url;
                        if (empty($url)) {
                            $defaultUrl = config('ojs.base_url');
                            if (!empty($defaultUrl)) {
                                $defaultHost = parse_url($defaultUrl, PHP_URL_HOST);
                                return $defaultHost ?: str_replace(['https://', 'http://', '/'], '', $defaultUrl);
                            }
                            return 'Default';
                        }
                        $host = parse_url($url, PHP_URL_HOST);
                        if (empty($host)) {
                            $host = str_replace(['https://', 'http://', '/'], '', $url);
                        }
                        return $host;
                    }),
            ])
            ->defaultGroup('ojs_base_url')
            ->groupingSettingsHidden()
            ->filters([
                SelectFilter::make('ojs_base_url')
                    ->label('Filter Website OJS')
                    ->placeholder('Semua Website')
                    ->options(function () {
                        $dbUrls = \App\Models\Journal::query()
                            ->whereNotNull('ojs_base_url')
                            ->where('ojs_base_url', '<>', '')
                            ->distinct()
                            ->pluck('ojs_base_url')
                            ->toArray();
                        
                        $urls = [];
                        $defaultUrl = config('ojs.base_url');
                        $defaultHost = 'Default';
                        if (!empty($defaultUrl)) {
                            $parsed = parse_url($defaultUrl, PHP_URL_HOST);
                            $defaultHost = $parsed ?: str_replace(['https://', 'http://', '/'], '', $defaultUrl);
                        }
                        $urls['default_env'] = $defaultHost;
                        
                        foreach ($dbUrls as $url) {
                            $host = parse_url($url, PHP_URL_HOST);
                            if (empty($host)) {
                                $host = str_replace(['https://', 'http://', '/'], '', $url);
                            }
                            $urls[$url] = $host ?: $url;
                        }
                        
                        return $urls;
                    })
                    ->default('default_env')
                    ->query(function ($query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        if ($data['value'] === 'default_env') {
                            return $query->where(function ($q) {
                                $q->whereNull('ojs_base_url')
                                  ->orWhere('ojs_base_url', '');
                            });
                        }

                        return $query->where('ojs_base_url', $data['value']);
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
