<?php

namespace App\Filament\Resources\Submissions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;

class SubmissionForm
{

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('status')
                    ->badge()
                    ->color(fn($record): string => match ($record->status) {
                        'Pending' => 'warning',
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                    })
                    ->columnSpanFull(),
                Section::make('Form Submission')
                    ->columnSpan(fn($record) => $record?->status === 'Approved' ? 6 : 4)
                    ->description('Data form submission')
                    ->schema([
                        Hidden::make('user_id')
                            ->default(Auth::user()->id),
                        TextInput::make('author_name')
                            ->default(Auth::user()->name)
                            ->required()
                            ->disabled(fn($record) => ! Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->disabled(fn($record) => ! Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),


                        TextInput::make('title')
                            ->columnSpanFull()
                            ->required()
                            ->disabled(fn($record) => ! Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),
                        TextInput::make('institution')
                            ->columnSpanFull()
                            ->required()
                            ->disabled(fn($record) => ! Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),
                        Select::make('journal_id')
                            ->relationship('journal', 'name')
                            ->disabled(fn($record) => ! Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending'])),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('vol')
                                    ->disabled(fn($record) => ! Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending']))
                                    ->live()
                                    ->formatStateUsing(function ($record) {
                                        if ($record && $record->volume) {
                                            if (preg_match('/Vol\.\s*(.*?)\s+No\.\s*(.*?)\s+(?:Tahun\s+|\()(.*?)\)?$/i', $record->volume, $matches)) {
                                                return $matches[1] ?? null;
                                            }
                                        }
                                        return null;
                                    })
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $vol = $get('vol') ?? '';
                                        $no = $get('no') ?? '';
                                        $year = $get('year') ?? '';
                                        $set('volume', trim('Vol. ' . $vol . ' ' . 'No. ' . $no . ' ' . '(' . $year . ')'));
                                    }),



                                TextInput::make('no')
                                    ->disabled(fn($record) => ! Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending']))
                                    ->live()
                                    ->formatStateUsing(function ($record) {
                                        if ($record && $record->volume) {
                                            if (preg_match('/Vol\.\s*(.*?)\s+No\.\s*(.*?)\s+(?:Tahun\s+|\()(.*?)\)?$/i', $record->volume, $matches)) {
                                                return $matches[2] ?? null;
                                            }
                                        }
                                        return null;
                                    })
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $vol = $get('vol') ?? '';
                                        $no = $get('no') ?? '';
                                        $year = $get('year') ?? '';
                                        $set('volume', trim('Vol. ' . $vol . ' ' . 'No. ' . $no . ' ' . '(' . $year . ')'));
                                    }),



                                TextInput::make('year')
                                    ->disabled(fn($record) => ! Auth::user()?->hasRole('super_admin') && in_array($record?->status, ['Approved', 'Pending']))
                                    ->live()
                                    ->formatStateUsing(function ($record) {
                                        if ($record && $record->volume) {
                                            if (preg_match('/Vol\.\s*(.*?)\s+No\.\s*(.*?)\s+(?:Tahun\s+|\()(.*?)\)?$/i', $record->volume, $matches)) {
                                                return $matches[3] ?? null;
                                            }
                                        }
                                        return null;
                                    })
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $vol = $get('vol') ?? '';
                                        $no = $get('no') ?? '';
                                        $year = $get('year') ?? '';
                                        $set('volume', trim('Vol. ' . $vol . ' ' . 'No. ' . $no . ' ' . '(' . $year . ')'));
                                    }),


                                Hidden::make('volume')
                            ]),
                        DatePicker::make('date_of_loa')
                            ->native(false)
                            ->label('Tanggal LOA')
                            ->required(),
                        TextInput::make('publication_link')
                            ->columnSpanFull()
                            ->label('Publication Link')
                            ->required(),

                        DatePicker::make('submission_date')
                            ->default(now())
                            ->native(false)
                            ->disabled()
                            ->visible(fn() => Auth::user()?->hasRole('super_admin')),
                        Hidden::make('submission_date')
                            ->default(now())
                            ->visible(fn() => ! Auth::user()?->hasRole('super_admin')),

                        Select::make('status')
                            ->options(['Pending' => 'Pending', 'Approved' => 'Approved', 'Rejected' => 'Rejected'])
                            ->default(fn($record) => $record?->status ?? 'Pending')
                            ->required()
                            ->visible(fn() => Auth::user()?->hasRole('super_admin')),
                        Hidden::make('status')
                            ->afterStateHydrated(function (Set $set, $state, $record) {
                                // If status is Rejected and user is not admin, reset to Pending
                                if ($record?->status === 'Rejected' && !Auth::user()?->hasRole('super_admin')) {
                                    $set('status', 'Pending');
                                }
                            })
                            ->dehydrated(function ($state, $record) {
                                // For non-admin users with rejected submissions, always save as Pending
                                return !Auth::user()?->hasRole('super_admin') && $record?->status === 'Rejected' ? 'Pending' : $state;
                            })
                            ->visible(fn() => ! Auth::user()?->hasRole('super_admin')),
                    ])->columns(2),
                Section::make('Pembayaran')
                    ->columnSpan(2)
                    ->description('Bukti Pembayaran')
                    ->visible(fn($record) => $record?->status !== 'Approved')
                    ->schema([
                        FileUpload::make('proof_of_payment')
                            ->label('Upload Bukti Pembayaran')
                            ->directory('proof-of-payment')
                            ->disk('public')
                            ->image()
                            ->required(),
                    ]),
            ])->columns(6);
    }
}
