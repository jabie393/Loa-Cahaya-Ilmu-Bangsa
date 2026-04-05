<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class AdminDashboardWidget extends BaseWidget
{
    public static function canView(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user && $user->hasRole('super_admin');
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', '192')
                ->description('32 increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Active Sessions', '21')
                ->description('7% decrease')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('System Health', 'Good')
                ->description('All systems operational')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}
