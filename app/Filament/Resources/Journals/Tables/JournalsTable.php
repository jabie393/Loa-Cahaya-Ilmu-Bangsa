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
                    ->placeholder('Default (.env)')
                    ->searchable()
                    ->sortable(),
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
                    ->getTitleFromRecordUsing(fn ($record) => $record->ojs_base_url ?: 'Default (.env)'),
            ])
            ->defaultGroup('ojs_base_url')
            ->groupingSettingsHidden()
            ->filters([
                SelectFilter::make('ojs_base_url')
                    ->label('Filter Website OJS')
                    ->placeholder('Semua Website')
                    ->options(function () {
                        $urls = \App\Models\Journal::query()
                            ->whereNotNull('ojs_base_url')
                            ->where('ojs_base_url', '<>', '')
                            ->distinct()
                            ->pluck('ojs_base_url', 'ojs_base_url')
                            ->toArray();
                        
                        $defaultUrl = config('ojs.base_url') ?: 'Default (.env)';
                        $urls['default_env'] = $defaultUrl . ' (Default)';
                        
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
