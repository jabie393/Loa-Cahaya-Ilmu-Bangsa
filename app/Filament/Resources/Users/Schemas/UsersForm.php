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
                    Grid::make(12)
                        ->columnSpanFull()
                        ->schema([
                            // Left Column: User Profile Info (5/12 width)
                            Grid::make(1)
                                ->schema([
                                    TextInput::make('name')
                                        ->label('Nama')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('password')
                                        ->password()
                                        ->dehydrated(fn($state) => filled($state))
                                        ->required(fn($operation) => $operation === 'create')
                                        ->label(__('Password')),

                                    TextInput::make('email')
                                        ->email()
                                        ->required()
                                        ->maxLength(255)
                                        ->label(__('Email')),

                                    TextInput::make('phone')
                                        ->label('Nomor Telepon'),

                                    Select::make('roles')
                                        ->label('Role')
                                        ->relationship('roles', 'name')
                                        ->preload()
                                        ->required()
                                        ->live(),
                                ])
                                ->columnSpan([
                                    'default' => 12,
                                    'lg' => 5,
                                ]),

                            // Right Column: Quota Management (7/12 width)
                            Section::make('Manajemen Kuota')
                                ->description('Statistik penggunaan review dan plagiarism.')
                                ->schema([
                                    Placeholder::make('review_header')->label('REVIEW JURNAL')->content(''),
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
                                                ->label('Review Credits (Manual)')
                                                ->helperText('Input angka untuk tambahan review credits.')
                                                ->numeric()
                                                ->default(0)
                                                ->required(),
                                        ]),

                                    Placeholder::make('plagiarism_divider')
                                        ->label('')
                                        ->content(fn() => new \Illuminate\Support\HtmlString('<hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 1rem 0;">')),

                                    Placeholder::make('plagiarism_header')->label('CEK PLAGIASI')->content(''),

                                    Grid::make(3)
                                        ->schema([
                                            Placeholder::make('plagiarism_daily_used_stats')
                                                ->label('Sisa Kuota Hari Ini')
                                                ->content(fn($record) => $record?->hasRole('super_admin')
                                                    ? 'Unlimited'
                                                    : (max(0, ($record?->userPlagiarismQuota?->daily_limit ?? config('quota.plagiarism_daily_limit')) - ($record?->userPlagiarismQuota?->daily_used ?? 0)) . ' / ' . ($record?->userPlagiarismQuota?->daily_limit ?? config('quota.plagiarism_daily_limit')))),

                                            Placeholder::make('additional_credits_stats')
                                                ->label('Plagiarism Credits')
                                                ->content(fn($record) => $record?->hasRole('super_admin') ? 'Unlimited' : ($record?->userPlagiarismQuota?->additional_credits ?? 0)),

                                            Placeholder::make('plagiarism_total_used_stats')
                                                ->label('Total Cek')
                                                ->content(fn($record) => $record?->userPlagiarismQuota?->total_used ?? 0),
                                        ]),

                                    Grid::make(1)
                                        ->relationship('userPlagiarismQuota')
                                        ->schema([
                                            TextInput::make('additional_credits')
                                                ->label('Plagiarism Credits (Manual)')
                                                ->helperText('Input angka untuk tambahan plagiarism credits.')
                                                ->numeric()
                                                ->default(0)
                                                ->required(),
                                        ]),
                                ])
                                ->columnSpan([
                                    'default' => 12,
                                    'lg' => 7,
                                ])
                                ->visible(fn($operation) => $operation === 'edit'),
                        ])
                ]
            );
    }
}
