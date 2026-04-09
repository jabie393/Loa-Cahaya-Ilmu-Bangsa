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

class SubmissionForm
{
    
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                    Section::make('Form Submission')
                            ->columnSpan(3)
                            ->description('Data Submission')
                            ->schema([
                            Hidden::make('user_id')
                                ->default(Auth::user()->id),
                            TextInput::make('author_name')
                                ->default(Auth::user()->name)
                                ->required()
                                ->disabled(fn () => ! Auth::user()?->hasRole('super_admin')),
                            TextInput::make('email')
                                ->label('Email address')
                                ->email()
                                ->required()
                                ->disabled(fn () => ! Auth::user()?->hasRole('super_admin')),
                            
                            TextInput::make('title')
                                ->required()
                                ->disabled(fn () => ! Auth::user()?->hasRole('super_admin')),
                            TextInput::make('institution')
                                ->required()
                                ->disabled(fn () => ! Auth::user()?->hasRole('super_admin')),
                            Select::make('journal_id')
                                ->relationship('journal', 'name')
                                ->disabled(fn () => ! Auth::user()?->hasRole('super_admin')),
                            TextInput::make('volume')
                            ->disabled(fn () => ! Auth::user()?->hasRole('super_admin')),
                            TextInput::make('publication_link')
                                ->label('Publication Link')
                                ->required(),
                            
                            
                            DatePicker::make('submission_date')
                                ->default(now())
                                ->disabled()
                                ->visible(fn () => Auth::user()?->hasRole('super_admin')),
                            Hidden::make('submission_date')
                                ->default(now())
                                ->visible(fn () => ! Auth::user()?->hasRole('super_admin')),
                                
                            Select::make('status')
                                ->options(['Pending' => 'Pending', 'Approved' => 'Approved'])
                                ->default('Pending')
                                ->required()
                                ->visible(fn () => Auth::user()?->hasRole('super_admin')),
                            Hidden::make('status')
                                ->default('Pending')
                                ->visible(fn () => ! Auth::user()?->hasRole('super_admin')),
                        ])->columns(2),
                    Section::make('Pembayaran')
                        ->columnSpan(2)
                        ->description('Bukti Pembayaran')
                        ->visible(fn ($record) => $record?->status !== 'Approved' )
                        ->schema([
                            FileUpload::make('proof_of_payment')
                                ->label('Upload Bukti Pembayaran')
                                ->directory('proof-of-payment')
                                ->disk('public')
                                ->image()
                                ->required(),
                        ]),
                    ])->columns(5);
                    
                
    }
}
