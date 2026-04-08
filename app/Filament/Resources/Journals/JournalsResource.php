<?php

namespace App\Filament\Resources\Journals;

use App\Filament\Resources\Journals\Pages\CreateJournals;
use App\Filament\Resources\Journals\Pages\EditJournals;
use App\Filament\Resources\Journals\Pages\ListJournals;
use App\Filament\Resources\Journals\Schemas\JournalsForm;
use App\Filament\Resources\Journals\Tables\JournalsTable;
use App\Models\Journal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use BezhanSalleh\FilamentShield\Traits\HasResourceShield;

class JournalsResource extends Resource
{
    protected static ?string $model = Journal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'journals';

    public static function form(Schema $schema): Schema
    {
        return JournalsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JournalsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJournals::route('/'),
            'create' => CreateJournals::route('/create'),
            'edit' => EditJournals::route('/{record}/edit'),
        ];
    }
}
