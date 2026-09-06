<?php

namespace App\Filament\Resources\Submissions\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Components\Section;
use Closure;

class SubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Form Submission')
                    ->columnSpanFull()
                    ->description('Lengkapi atau perbarui data pengajuan di bawah ini.')
                    ->schema([
                        Hidden::make('user_id')
                            ->default(Auth::user()->id),

                        Select::make('want_doi')
                            ->label('Request DOI')
                            ->placeholder('Pilih Opsi DOI')
                            ->options(function ($record) {
                                if ($record !== null && !empty($record->repository_identifier)) {
                                    return [
                                        ($record->want_doi ? 1 : 0) => $record->repository_identifier,
                                    ];
                                }
                                return [
                                    1 => 'Dengan DOI',
                                    0 => 'Tanpa DOI',
                                ];
                            })
                            ->formatStateUsing(fn ($state) => $state !== null ? ($state ? 1 : 0) : null)
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record !== null && $record->want_doi !== null) {
                                    $component->state($record->want_doi ? 1 : 0);
                                }
                            })
                            ->helperText('Pilihan ini mempengaruhi nominal tarif pembayaran QRIS secara otomatis.')
                            ->disabled(fn($record) => ($record !== null && !empty($record->repository_identifier)) || ($record !== null && !Auth::user()?->hasRole('super_admin') && $record->status === 'Approved'))
                            ->required(),

                        FileUpload::make('manuscript_file')
                            ->label('File Naskah PDF yang telah disesuaikan Template')
                            ->required()
                            ->acceptedFileTypes([
                                'application/pdf',
                            ])
                            ->maxSize(20480)
                            ->disk('public')
                            ->directory('manuscripts')
                            ->downloadable()
                            ->preserveFilenames()
                            ->rules([
                                function ($get, $record) {
                                    return function (string $attribute, $value, Closure $fail) use ($get, $record) {
                                        $journalId = $get('journal_id') ?? $record?->journal_id;
                                        if (!$journalId) {
                                            return;
                                        }

                                        $journal = \App\Models\Journal::find($journalId);
                                        if (!$journal) {
                                            return;
                                        }

                                        $filePath = null;
                                        if (is_object($value) && method_exists($value, 'getRealPath')) {
                                            $filePath = $value->getRealPath();
                                        } elseif (is_string($value)) {
                                            $filePath = storage_path('app/public/' . $value);
                                            if (!file_exists($filePath)) {
                                                $filePath = \Illuminate\Support\Facades\Storage::path($value);
                                            }
                                        }

                                        if (!$filePath || !file_exists($filePath)) {
                                            $fail('Berkas naskah tidak ditemukan.');
                                            return;
                                        }

                                        try {
                                            $parser = new \Smalot\PdfParser\Parser();
                                            $pdf = $parser->parseFile($filePath);
                                            $pages = $pdf->getPages();
                                            if (empty($pages)) {
                                                $fail('Berkas PDF kosong atau rusak.');
                                                return;
                                            }

                                            $firstPageText = $pages[0]->getText();
                                            if (empty($firstPageText)) {
                                                $fail('Teks naskah tidak dapat terbaca. Pastikan Anda mengunggah naskah digital (bukan hasil scan/foto) yang disalin ke template.');
                                                return;
                                            }

                                            $text = strtolower($firstPageText);
                                            $slug = strtolower($journal->slug);
                                            $name = strtolower($journal->name);

                                            $nameParts = explode(':', $journal->name);
                                            $firstWord = strtolower(trim($nameParts[0]));

                                            // 1. Pengecekan berbasis database identifier (Semua keyword harus ada / AND Logic)
                                            if (!empty($journal->identifier)) {
                                                $dbKeywords = array_map('trim', explode(',', $journal->identifier));
                                                $missingKeywords = [];
                                                
                                                foreach ($dbKeywords as $kw) {
                                                    if (!empty($kw) && !str_contains($text, strtolower($kw))) {
                                                        $missingKeywords[] = $kw;
                                                    }
                                                }
                                                
                                                if (!empty($missingKeywords)) {
                                                    $fail("Pastikan naskah artikel disesuaikan dengan template {$journal->name} yang sudah disediakan");
                                                }
                                            } else {
                                                // 2. Fallback jika identifier di database kosong (Cukup salah satu / OR Logic)
                                                $found = false;
                                                if (str_contains($text, $slug)) $found = true;
                                                if (str_contains($text, $name)) $found = true;
                                                if (str_contains($text, $firstWord)) $found = true;
                                                
                                                if (!$found) {
                                                    $fail("Pastikan naskah artikel disesuaikan dengan template {$journal->name} yang sudah disediakan");
                                                }
                                            }
                                        } catch (\Exception $e) {
                                            $fail('Gagal memeriksa format template naskah: ' . $e->getMessage());
                                        }
                                    };
                                }
                            ])
                            ->disabled(fn($record) => $record !== null && !Auth::user()?->hasRole('super_admin') && $record->status === 'Approved'),

                        TextInput::make('email')
                            ->label('Email Korespondensi (Penerima LOA)')
                            ->email()
                            ->required()
                            ->default(fn() => Auth::user()?->email)
                            ->placeholder('email@example.com')
                            ->disabled(fn($record) => $record !== null && !Auth::user()?->hasRole('super_admin') && $record->status === 'Approved'),
                    ]),
            ])
            ->columns(1);
    }
}

