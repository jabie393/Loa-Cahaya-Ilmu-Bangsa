<?php

namespace App\Filament\Resources\SubmissionPricings\Schemas;

use App\Models\SubmissionPricing;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class SubmissionPricingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Placeholder::make('tier_info')
                    ->label('')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->content(function (?SubmissionPricing $record): HtmlString {
                        if (! $record) {
                            return new HtmlString('');
                        }

                        $categoryLabel = match ($record->category) {
                            'issn' => 'ISSN (Nasional)',
                            'international' => 'International',
                            'addon' => 'Add-on Layanan',
                            'setting' => 'Parameter Global',
                            default => ucfirst($record->category),
                        };

                        $categoryClass = match ($record->category) {
                            'issn' => 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border-sky-200 dark:border-sky-800',
                            'international' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
                            'addon' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                            default => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                        };

                        $notesHtml = $record->notes
                            ? "<div class=\"text-xs text-gray-500 dark:text-gray-400 mt-1\">{$record->notes}</div>"
                            : '';

                        return new HtmlString("
                            <div class=\"p-3.5 rounded-xl bg-gray-50 dark:bg-white/[0.04] border border-gray-200/80 dark:border-white/10 flex items-start justify-between gap-3\">
                                <div>
                                    <div class=\"font-bold text-sm text-gray-900 dark:text-white\">{$record->tier_name}</div>
                                    {$notesHtml}
                                </div>
                                <span class=\"inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {$categoryClass}\">
                                    {$categoryLabel}
                                </span>
                            </div>
                        ");
                    }),

                TextInput::make('gross_amount')
                    ->label(fn (?SubmissionPricing $record) => $record?->key === 'setting_mdr_rate' ? 'Rate MDR (Desimal)' : 'Total Biaya / Tarif')
                    ->numeric()
                    ->prefix(fn (?SubmissionPricing $record) => $record?->key === 'setting_mdr_rate' ? null : 'Rp')
                    ->required()
                    ->live(debounce: 300)
                    ->columnSpan(fn (?SubmissionPricing $record) => $record?->category === 'setting' ? 2 : 1)
                    ->helperText(fn (?SubmissionPricing $record) => $record?->key === 'setting_mdr_rate'
                        ? 'Contoh: 0.007 untuk 0.7%'
                        : 'Nominal yang dibayar oleh pemohon/penulis.'),

                TextInput::make('developer_gross_share')
                    ->label('Bagian Developer')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->live(debounce: 300)
                    ->hidden(fn (?SubmissionPricing $record) => $record?->category === 'setting')
                    ->columnSpan(1)
                    ->helperText('Hak bagian kotor untuk developer.'),

                Placeholder::make('journal_share_preview')
                    ->label('')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->hidden(fn (?SubmissionPricing $record) => $record?->category === 'setting')
                    ->content(function ($get): HtmlString {
                        $gross = (float) ($get('gross_amount') ?? 0);
                        $dev = (float) ($get('developer_gross_share') ?? 0);
                        $journal = max(0, $gross - $dev);

                        $formattedJournal = 'Rp ' . number_format($journal, 0, ',', '.');
                        $formattedGross = 'Rp ' . number_format($gross, 0, ',', '.');
                        $formattedDev = 'Rp ' . number_format($dev, 0, ',', '.');

                        return new HtmlString("
                            <div class=\"p-3.5 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60\">
                                <div class=\"flex items-center justify-between\">
                                    <div>
                                        <div class=\"text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300\">Estimasi Hak Jurnal</div>
                                        <div class=\"text-xs text-emerald-600 dark:text-emerald-400 mt-0.5\">Rumus: {$formattedGross} − {$formattedDev}</div>
                                    </div>
                                    <div class=\"text-lg font-extrabold text-emerald-600 dark:text-emerald-400\">
                                        {$formattedJournal}
                                    </div>
                                </div>
                            </div>
                        ");
                    }),
            ]);
    }
}
