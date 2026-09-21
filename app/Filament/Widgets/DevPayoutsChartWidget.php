<?php

namespace App\Filament\Widgets;

use App\Models\DevPayout;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class DevPayoutsChartWidget extends ChartWidget
{
    protected ?string $heading = 'Tren Pencairan';
    protected ?string $maxHeight = '360px';
    protected int | string | array $columnSpan = 'full';
    protected ?string $pollingInterval = '10s';
    protected string $color = 'info';

    public ?string $filter = 'all';

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('ryu_dev') ?? false;
    }

    #[On('payout-created')]
    #[On('payout-updated')]
    public function refreshData(): void
    {
        $this->cachedData = null;
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            'all' => 'Semua Waktu (Dari Awal)',
            '30d' => '30 Hari Terakhir',
            '7d' => '7 Hari Terakhir',
            'month' => 'Bulan Ini',
            'year' => 'Tahun Ini',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter ?: 'all';
        $now = Carbon::now();

        $labels = [];
        $data = [];

        if ($activeFilter === '7d') {
            $startDate = $now->copy()->subDays(6)->startOfDay();
            $endDate = $now->copy()->endOfDay();

            $payouts = DevPayout::query()
                ->whereIn('status', ['confirmed', 'completed'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get(['amount', 'created_at'])
                ->groupBy(fn ($item) => $item->created_at->format('Y-m-d'))
                ->map(fn ($group) => (float) $group->sum('amount'));

            $period = CarbonPeriod::create($startDate, '1 day', $endDate);
            foreach ($period as $date) {
                $key = $date->format('Y-m-d');
                $labels[] = $date->translatedFormat('d M');
                $data[] = (float) ($payouts->get($key) ?? 0);
            }
        } elseif ($activeFilter === '30d') {
            $startDate = $now->copy()->subDays(29)->startOfDay();
            $endDate = $now->copy()->endOfDay();

            $payouts = DevPayout::query()
                ->whereIn('status', ['confirmed', 'completed'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get(['amount', 'created_at'])
                ->groupBy(fn ($item) => $item->created_at->format('Y-m-d'))
                ->map(fn ($group) => (float) $group->sum('amount'));

            $period = CarbonPeriod::create($startDate, '1 day', $endDate);
            foreach ($period as $date) {
                $key = $date->format('Y-m-d');
                $labels[] = $date->translatedFormat('d M');
                $data[] = (float) ($payouts->get($key) ?? 0);
            }
        } elseif ($activeFilter === 'month') {
            $startDate = $now->copy()->startOfMonth()->startOfDay();
            $endDate = $now->copy()->endOfMonth()->endOfDay();

            $payouts = DevPayout::query()
                ->whereIn('status', ['confirmed', 'completed'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get(['amount', 'created_at'])
                ->groupBy(fn ($item) => $item->created_at->format('Y-m-d'))
                ->map(fn ($group) => (float) $group->sum('amount'));

            $period = CarbonPeriod::create($startDate, '1 day', $endDate);
            foreach ($period as $date) {
                $key = $date->format('Y-m-d');
                $labels[] = $date->format('d M');
                $data[] = (float) ($payouts->get($key) ?? 0);
            }
        } elseif ($activeFilter === 'year') {
            $startDate = $now->copy()->startOfYear()->startOfDay();
            $endDate = $now->copy()->endOfYear()->endOfDay();

            $payouts = DevPayout::query()
                ->whereIn('status', ['confirmed', 'completed'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get(['amount', 'created_at'])
                ->groupBy(fn ($item) => $item->created_at->format('Y-m'))
                ->map(fn ($group) => (float) $group->sum('amount'));

            for ($m = 1; $m <= 12; $m++) {
                $monthDate = Carbon::create($now->year, $m, 1);
                $key = $monthDate->format('Y-m');
                $labels[] = $monthDate->translatedFormat('M Y');
                $data[] = (float) ($payouts->get($key) ?? 0);
            }
        } else {
            // Default: 'all' (From first payout to last/present)
            $firstPayout = DevPayout::query()
                ->whereIn('status', ['confirmed', 'completed'])
                ->oldest('created_at')
                ->first();

            $startDate = $firstPayout ? $firstPayout->created_at->copy()->startOfDay() : $now->copy()->subDays(29)->startOfDay();
            $endDate = $now->copy()->endOfDay();

            $daysDifference = $startDate->diffInDays($endDate);

            if ($daysDifference <= 31) {
                // If 1 month or less, show daily breakdown
                $payouts = DevPayout::query()
                    ->whereIn('status', ['confirmed', 'completed'])
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get(['amount', 'created_at'])
                    ->groupBy(fn ($item) => $item->created_at->format('Y-m-d'))
                    ->map(fn ($group) => (float) $group->sum('amount'));

                $period = CarbonPeriod::create($startDate, '1 day', $endDate);
                foreach ($period as $date) {
                    $key = $date->format('Y-m-d');
                    $labels[] = $date->translatedFormat('d-M-y');
                    $data[] = (float) ($payouts->get($key) ?? 0);
                }
            } else {
                // If span is more than a month, show monthly breakdown from first payout month to now
                $payouts = DevPayout::query()
                    ->whereIn('status', ['confirmed', 'completed'])
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get(['amount', 'created_at'])
                    ->groupBy(fn ($item) => $item->created_at->format('Y-m'))
                    ->map(fn ($group) => (float) $group->sum('amount'));

                $period = CarbonPeriod::create($startDate->copy()->startOfMonth(), '1 month', $endDate->copy()->endOfMonth());
                foreach ($period as $date) {
                    $key = $date->format('Y-m');
                    $labels[] = $date->translatedFormat('M Y');
                    $data[] = (float) ($payouts->get($key) ?? 0);
                }
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Payout Cair (Rp)',
                    'data' => $data,
                    'fill' => 'start',
                    'tension' => 0.4,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
                    'pointBackgroundColor' => '#3b82f6',
                    'pointBorderColor' => '#ffffff',
                    'pointHoverBackgroundColor' => '#ffffff',
                    'pointHoverBorderColor' => '#3b82f6',
                    'pointRadius' => 2,
                    'pointHoverRadius' => 5,
                    'pointHitRadius' => 20,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array | RawJs | null
    {
        return RawJs::make(<<<'JS'
        {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 6,
                        padding: 8,
                        font: {
                            family: 'inherit',
                            size: 11,
                            weight: 600
                        }
                    }
                },
                tooltip: {
                    padding: 8,
                    cornerRadius: 6,
                    callbacks: {
                        label: function(context) {
                            let value = context.parsed.y || 0;
                            return ' Total: ' + new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0
                            }).format(value);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    border: {
                        display: true,
                        color: 'rgba(156, 163, 175, 0.2)'
                    },
                    ticks: {
                        maxTicksLimit: 5,
                        font: {
                            size: 10
                        },
                        callback: function(value) {
                            if (value >= 1000000) {
                                return 'Rp ' + (value / 1000000).toLocaleString('id-ID') + ' jt';
                            }
                            if (value >= 1000) {
                                return 'Rp ' + (value / 1000).toLocaleString('id-ID') + ' rb';
                            }
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    },
                    grid: {
                        color: 'rgba(156, 163, 175, 0.12)'
                    }
                },
                x: {
                    border: {
                        display: true,
                        color: 'rgba(156, 163, 175, 0.2)'
                    },
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                }
            }
        }
        JS);
    }
}
