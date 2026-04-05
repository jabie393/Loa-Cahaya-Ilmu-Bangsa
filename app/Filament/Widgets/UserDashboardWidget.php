<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class UserDashboardWidget extends BaseWidget
{
    public static function canView(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user && $user->hasRole('panel_user');
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Your Documents', '12')
                ->description('3 awaiting review')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
            Stat::make('Pending Actions', '2')
                ->description('Please complete today')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning'),
        ];
    }
}
