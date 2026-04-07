<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class UserDashboardWidget extends BaseWidget
{
    use HasWidgetShield;

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
