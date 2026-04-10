<?php

namespace App\Filament\Pages;

use App\Models\Submission;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class MyCertificates extends Page
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected string $view = 'filament.pages.my-certificates';

    protected static ?string $title = 'My Certificates';

    protected static ?string $navigationLabel = 'My Certificates';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return true;
    }

    public Collection $submissions;

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
