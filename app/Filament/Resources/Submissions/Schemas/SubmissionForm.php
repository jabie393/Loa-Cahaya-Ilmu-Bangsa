<?php

namespace App\Filament\Resources\Submissions\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Components\Section;

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
                            ->getUploadedFileNameForStorageUsing(function ($file, $record) {
                                if ($record) {
                                    $extension = $file->getClientOriginalExtension();
                                    return "file-{$record->id}" . ($extension ? ".{$extension}" : "");
                                }
                                return $file->getClientOriginalName();
                            })
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
