<?php

namespace App\Filament\Resources\Submissions;

use App\Filament\Resources\Submissions\Pages\CreateSubmission;
use App\Filament\Resources\Submissions\Pages\EditSubmission;
use App\Filament\Resources\Submissions\Pages\ListSubmissions;
use App\Filament\Resources\Submissions\Schemas\SubmissionForm;
use App\Filament\Resources\Submissions\Tables\SubmissionsTable;
use App\Models\Submission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SubmissionResource extends Resource
{

    protected static ?string $model = Submission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = '3. Quick Submit';
    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'id';


    public static function getNavigationBadge(): ?string
    {
        if (Auth::user()->hasRole('super_admin')) {
            $count = static::getModel()::where('status', 'pending')->count();
        } else {
            $count = static::getModel()::where('user_id', Auth::id())
                ->where('status', 'Rejected')
                ->count();
        }

        return $count > 0 ? (string) $count : null;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        $selectRaw = "CASE 
            WHEN review_status = 'failed' THEN 0
            WHEN ojs_status = 'failed' THEN 1
            WHEN status = 'Pending' THEN 2
            WHEN ojs_status = 'pending' THEN 3
            WHEN review_status = 'reviewed' THEN 4
            WHEN status = 'Approved' THEN 5
            WHEN ojs_status = 'submitted' THEN 6
            ELSE 7
        END AS sort_priority";

        if (Auth::user()->hasRole('super_admin')) {
            $query->where(function ($q) {
                $q->where('status', '!=', 'Draft')
                    ->orWhere('user_id', Auth::id());
            });
        } else {
            $query->where('user_id', Auth::id());
        }

        return $query->select('*')->selectRaw($selectRaw);
    }

    public static function form(Schema $schema): Schema
    {
        return SubmissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubmissionsTable::configure($table);
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
            'index' => ListSubmissions::route('/'),
            'create' => CreateSubmission::route('/create'),
            'edit' => EditSubmission::route('/{record}/edit'),
            'review' => Pages\ReviewSubmission::route('/{record}/review'),
            'view' => Pages\ReviewSubmission::route('/{record}/view'),
            'preview' => Pages\PreviewLoa::route('/{record}/preview'),
            'preview_ac' => Pages\PreviewCertificate::route('/{record}/ac'),
            'preview_pfc' => Pages\PreviewPfc::route('/{record}/pfc'),
        ];
    }
}
