<?php

namespace App\Filament\Pages;

use App\Models\Submission;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use UnitEnum;

class MyCertificates extends Page
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected string $view = 'filament.pages.my-certificates';

    protected static ?string $title = 'My Certificates';

    protected static ?string $navigationLabel = 'My Certificates';

    
    public static function canAccess(): bool
    {
        return true;
    }

    public Collection $submissions;

    public static function getNavigationBadge(): ?string
    {
        $count = Submission::query()
            ->where('user_id', Auth::id())
            ->where('status', 'Approved')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public function mount(): void
    {
        $this->submissions = Submission::query()
            ->where('user_id', Auth::id())
            ->where('status', 'Approved')
            ->with('journal')
            ->latest('approved_date')
            ->get();
    }
}
