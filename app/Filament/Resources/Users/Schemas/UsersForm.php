<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class UsersForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(
                [
                    TextInput::make('name')
                        ->label('Nama')
                        ->required(),

                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique('users', 'email', ignoreRecord: true),

                    TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->extraInputAttributes(['autocomplete' => 'new-password'])
                        ->required(fn($operation) => $operation === 'create') // password is only required on create, not edit.
                        ->dehydrated(fn($state) => filled($state)) // prevents sending empty values to the database (so it doesn’t overwrite existing password).
                        ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null) // if user types something, hash it; otherwise leave as null.
                        ->label(__('Password')),

                    TextInput::make('phone')
                        ->label('Nomor Telepon'),

                    Select::make('roles')
                        ->label('Role')
                        ->relationship('roles', 'name')
                        ->preload()
                        ->required()
                        ->live(),

                    Section::make('Manajemen Kuota Review')
                        ->description('Statistik penggunaan review jurnal oleh user ini.')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    Placeholder::make('daily_used_stats')
                                        ->label('Sisa Kuota Hari Ini')
                                        ->content(fn($record) => $record?->hasRole('super_admin')
                                            ? 'Unlimited'
                                            : (max(0, ($record?->userQuota?->daily_limit ?? config('quota.default_daily_limit')) - ($record?->userQuota?->daily_used ?? 0)) . ' / ' . ($record?->userQuota?->daily_limit ?? config('quota.default_daily_limit')))),

                                    Placeholder::make('review_credits_stats')
                                        ->label('Review Credits')
                                        ->content(fn($record) => $record?->hasRole('super_admin') ? 'Unlimited' : ($record?->userQuota?->review_credits ?? 0)),

                                    Placeholder::make('total_used_stats')
                                        ->label('Total Review')
                                        ->content(fn($record) => $record?->userQuota?->total_used ?? 0),
                                ]),

                            Grid::make(1)
                                ->relationship('userQuota')
                                ->schema([
                                    TextInput::make('review_credits')
                                        ->label('Kuota Review (Manual)')
                                        ->helperText('Input angka di sini untuk memberikan tambahan kuota permanen bagi user.')
                                        ->numeric()
                                        ->default(0)
                                        ->required(),
                                ]),
                        ])
                        ->visible(fn($operation) => $operation === 'edit'),

                    Section::make('Manajemen Kuota Plagiasi')
                        ->description('Statistik penggunaan cek plagiasi oleh user ini.')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    Placeholder::make('plagiarism_daily_used_stats')
                                        ->label('Sisa Kuota Hari Ini')
                                        ->content(fn($record) => $record?->hasRole('super_admin')
                                            ? 'Unlimited'
                                            : (max(0, ($record?->userPlagiarismQuota?->daily_limit ?? config('quota.plagiarism_daily_limit')) - ($record?->userPlagiarismQuota?->daily_used ?? 0)) . ' / ' . ($record?->userPlagiarismQuota?->daily_limit ?? config('quota.plagiarism_daily_limit')))),

                                    Placeholder::make('additional_credits_stats')
                                        ->label('Kredit Tambahan')
                                        ->content(fn($record) => $record?->hasRole('super_admin') ? 'Unlimited' : ($record?->userPlagiarismQuota?->additional_credits ?? 0)),

                                    Placeholder::make('plagiarism_total_used_stats')
                                        ->label('Total Cek')
                                        ->content(fn($record) => $record?->userPlagiarismQuota?->total_used ?? 0),
                                ]),

                            Grid::make(1)
                                ->relationship('userPlagiarismQuota')
                                ->schema([
                                    TextInput::make('additional_credits')
                                        ->label('Kredit Tambahan (Manual)')
                                        ->helperText('Input angka di sini untuk memberikan tambahan kuota cek plagiasi bagi user.')
                                        ->numeric()
                                        ->default(0)
                                        ->required(),
                                ]),
                        ])
                        ->visible(fn($operation) => $operation === 'edit'),

                ]
            );
    }
}
