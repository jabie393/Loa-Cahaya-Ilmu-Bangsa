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
                        if (!$record) {
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
                    ->label(fn(?SubmissionPricing $record) => $record?->key === 'setting_mdr_rate' ? 'Rate MDR (Desimal)' : 'Total Biaya / Tarif')
                    ->numeric()
                    ->prefix(fn(?SubmissionPricing $record) => $record?->key === 'setting_mdr_rate' ? null : 'Rp')
                    ->required()
                    ->live(debounce: 300)
                    ->columnSpan(fn(?SubmissionPricing $record) => $record?->category === 'setting' ? 2 : 1)
                    ->helperText(fn(?SubmissionPricing $record) => $record?->key === 'setting_mdr_rate'
                        ? 'Contoh: 0.007 untuk 0.7%'
                        : 'Nominal dibayar author'),

                TextInput::make('developer_gross_share')
                    ->label('Bagian Developer')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->live(debounce: 300)
                    ->hidden(fn(?SubmissionPricing $record) => $record?->category === 'setting')
                    ->columnSpan(1)
                    ->helperText('Hak bagian kotor developer.'),

                Placeholder::make('revenue_share_preview')
                    ->label('')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->hidden(fn (?SubmissionPricing $record) => $record?->category === 'setting')
                    ->content(function ($get): HtmlString {
                        $gross = (float) ($get('gross_amount') ?? 0);
                        $devGross = (float) ($get('developer_gross_share') ?? 0);

                        $mdrRate = 0.007;
                        try {
                            $mdrRate = app(\App\Services\SubmissionPricingService::class)->getMdrRate();
                        } catch (\Throwable $e) {
                            $mdrRate = 0.007;
                        }

                        $mdrAmount = round($gross * $mdrRate);
                        $devNet = $devGross - $mdrAmount;
                        $journalShare = max(0, $gross - $devGross);

                        $mdrPercentLabel = ($mdrRate * 100) . '%';
                        $formattedJournal = 'Rp ' . number_format($journalShare, 0, ',', '.');
                        $formattedGross = 'Rp ' . number_format($gross, 0, ',', '.');
                        $formattedDevGross = 'Rp ' . number_format($devGross, 0, ',', '.');
                        $formattedMdr = 'Rp ' . number_format($mdrAmount, 0, ',', '.');
                        $formattedDevNet = 'Rp ' . number_format($devNet, 0, ',', '.');

                        return new HtmlString("
                            <div class=\"grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1\">
                                <!-- Card Hak Jurnal -->
                                <div class=\"p-3.5 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 flex flex-col justify-between\">
                                    <div>
                                        <div class=\"text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300\">Estimasi Hak Jurnal</div>
                                        <div class=\"text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1\">
                                            {$formattedJournal}
                                        </div>
                                    </div>
                                    <div class=\"text-[11px] text-emerald-700/80 dark:text-emerald-400/80 mt-2 pt-2 border-t border-emerald-200/60 dark:border-emerald-800/40\">
                                        Rumus: {$formattedGross} − {$formattedDevGross}
                                    </div>
                                </div>

                                <!-- Card Hak Bersih Developer -->
                                <div class=\"p-3.5 rounded-xl bg-sky-50/80 dark:bg-sky-950/30 border border-sky-200 dark:border-sky-800/60 flex flex-col justify-between\">
                                    <div>
                                        <div class=\"flex items-center justify-between\">
                                            <span class=\"text-xs font-bold uppercase tracking-wider text-sky-800 dark:text-sky-300\">Estimasi Bersih Dev</span>
                                            <span class=\"text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-100 dark:bg-sky-900 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800\">MDR {$mdrPercentLabel}</span>
                                        </div>
                                        <div class=\"text-xl font-black text-sky-600 dark:text-sky-400 mt-1\">
                                            {$formattedDevNet}
                                        </div>
                                    </div>
                                    <div class=\"text-[11px] text-sky-700/80 dark:text-sky-400/80 mt-2 pt-2 border-t border-sky-200/60 dark:border-sky-800/40\">
                                        Hak Kotor ({$formattedDevGross}) − MDR ({$formattedMdr})
                                    </div>
                                </div>
                            </div>
                        ");
                    }),
            ]);
    }
}
