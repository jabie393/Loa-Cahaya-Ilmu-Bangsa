<?php

namespace App\Filament\Resources\Submissions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class SubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('author_name')
                    ->default(Auth::user()->name)
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('institution')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                Select::make('journal_id')
                    ->relationship('journal', 'name'),
                TextInput::make('volume'),
                FileUpload::make('proof_of_payment'),
                DatePicker::make('submission_date')
                    ->default(now())
                    ->required()
                    ->hidden(fn () => ! Auth::user()?->hasRole('super_admin')),
                Select::make('status')
                    ->options(['Pending' => 'Pending', 'Approved' => 'Approved'])
                    ->default('Pending')
                    ->required()
                    ->hidden(fn () => ! Auth::user()?->hasRole('super_admin')),
            ]);
    }
}
