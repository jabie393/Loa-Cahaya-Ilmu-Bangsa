<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DevPayout;
use App\Models\Payment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeveloperFinancialController extends Controller
{
    /**
     * Get Developer Financial Summary and Chart Data.
     * Protected by 'ryu_dev' role check.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Role Authorization Verification
        if (!$user || !$user->hasRole('ryu_dev')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Fitur ini hanya dapat diakses oleh role developer.',
            ], 403);
        }

        // 2. Financial Metrics Calculation (Identical to DevPayoutsPage.php)
        $totalEarned = (int) Payment::where('payment_status', 'paid')->sum('developer_net_share');
        $totalTransferred = (int) DevPayout::whereIn('status', ['waiting_confirmation', 'confirmed', 'completed'])->sum('amount');
        $totalCommitted = (int) DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation', 'confirmed', 'completed'])->sum('amount');

        $pendingPayout = (int) DevPayout::where('status', 'waiting_payout')->sum('amount');
        $unpaidPayoutCount = (int) DevPayout::where('status', 'waiting_payout')->count();
        $unpaidBalance = max(0, $totalEarned - $totalCommitted);

        // 3. Payout Statistics
        $payoutSuccessful = (int) DevPayout::whereIn('status', ['waiting_confirmation', 'confirmed', 'completed'])->count();
        $payoutPending = (int) DevPayout::where('status', 'waiting_payout')->count();
        $payoutFailed = (int) DevPayout::where('status', 'rejected')->count();

        // 4. Trend Chart Calculation (Identical to DevPayoutsChartWidget.php)
        $filter = $request->query('period', '30d');
        $now = Carbon::now();
        $labels = [];
        $values = [];

        if ($filter === '7d') {
            $startDate = $now->copy()->subDays(6)->startOfDay();
            $endDate = $now->copy()->endOfDay();
            $period = CarbonPeriod::create($startDate, '1 day', $endDate);
        } elseif ($filter === 'month') {
            $startDate = $now->copy()->startOfMonth()->startOfDay();
            $endDate = $now->copy()->endOfMonth()->endOfDay();
            $period = CarbonPeriod::create($startDate, '1 day', $endDate);
        } else { // default '30d'
            $startDate = $now->copy()->subDays(29)->startOfDay();
            $endDate = $now->copy()->endOfDay();
            $period = CarbonPeriod::create($startDate, '1 day', $endDate);
        }

        $payouts = DevPayout::query()
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get(['amount', 'created_at'])
            ->groupBy(fn($item) => $item->created_at->format('Y-m-d'))
            ->map(fn($group) => (float) $group->sum('amount'));

        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->translatedFormat('d M');
            $values[] = (float) ($payouts->get($key) ?? 0);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_earned' => $totalEarned,
                    'total_transferred' => $totalTransferred,
                    'pending_payout' => $pendingPayout,
                    'today_earned' => $unpaidBalance, // Saldo Siap Cair
                    'unpaid_payout_count' => $unpaidPayoutCount,
                ],
                'payout_statistics' => [
                    'successful' => $payoutSuccessful,
                    'pending' => $payoutPending,
                    'failed' => $payoutFailed,
                ],
                'chart' => [
                    'period' => $filter,
                    'labels' => $labels,
                    'values' => $values,
                ],
                'last_updated' => Carbon::now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get paginated transactions with dev net share.
     */
    public function transactions(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('ryu_dev')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], 403);
        }

        $perPage = min(50, max(10, (int) $request->query('per_page', 20)));

        $payments = Payment::query()
            ->where('payment_status', 'paid')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Get paginated dev payout batches and records.
     */
    public function payouts(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('ryu_dev')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], 403);
        }

        $perPage = min(50, max(10, (int) $request->query('per_page', 20)));

        $payouts = DevPayout::query()
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $payouts,
        ]);
    }
}
