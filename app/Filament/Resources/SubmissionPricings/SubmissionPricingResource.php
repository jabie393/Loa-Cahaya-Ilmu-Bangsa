<?php

namespace App\Filament\Resources\SubmissionPricings;

use App\Filament\Resources\SubmissionPricings\Pages\ListSubmissionPricings;
use App\Filament\Resources\SubmissionPricings\Schemas\SubmissionPricingForm;
use App\Filament\Resources\SubmissionPricings\Tables\SubmissionPricingsTable;
use App\Models\SubmissionPricing;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SubmissionPricingResource extends Resource
{
    protected static ?string $model = SubmissionPricing::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Pricelist Setting';

    protected static ?string $title = 'Pricelist & Bagi Hasil';

    protected static ?string $slug = 'submission-pricings';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('ryu_dev') ?? false;
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->hasRole('ryu_dev') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->hasRole('ryu_dev') ?? false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return SubmissionPricingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubmissionPricingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubmissionPricings::route('/'),
        ];
    }
}
