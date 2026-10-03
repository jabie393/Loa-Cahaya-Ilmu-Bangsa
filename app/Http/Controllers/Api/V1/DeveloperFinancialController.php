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

        $query = Payment::query()
            ->with(['items:id,payment_id,item_name,item_type'])
            ->where('payment_status', 'paid');

        // 1. Period Filter (including custom Month & Year)
        $period = $request->query('period', 'all');
        $year = $request->query('year');
        $month = $request->query('month');
        $now = Carbon::now();

        if ($period === 'month_year' || ($year && $month)) {
            $query->where(function ($q) use ($year, $month) {
                $q->whereYear('paid_at', $year)->whereMonth('paid_at', $month)
                  ->orWhere(function ($sq) use ($year, $month) {
                      $sq->whereNull('paid_at')->whereYear('created_at', $year)->whereMonth('created_at', $month);
                  });
            });
        } elseif ($year) {
            $query->where(function ($q) use ($year) {
                $q->whereYear('paid_at', $year)
                  ->orWhere(function ($sq) use ($year) {
                      $sq->whereNull('paid_at')->whereYear('created_at', $year);
                  });
            });
        } elseif ($period === 'today') {
            $query->where(function ($q) use ($now) {
                $q->whereDate('paid_at', $now->toDateString())
                  ->orWhere(function ($sq) use ($now) {
                      $sq->whereNull('paid_at')->whereDate('created_at', $now->toDateString());
                  });
            });
        } elseif ($period === '7d') {
            $startDate = $now->copy()->subDays(6)->startOfDay();
            $query->where(function ($q) use ($startDate) {
                $q->where('paid_at', '>=', $startDate)
                  ->orWhere(function ($sq) use ($startDate) {
                      $sq->whereNull('paid_at')->where('created_at', '>=', $startDate);
                  });
            });
        } elseif ($period === 'month') {
            $startDate = $now->copy()->startOfMonth()->startOfDay();
            $query->where(function ($q) use ($startDate) {
                $q->where('paid_at', '>=', $startDate)
                  ->orWhere(function ($sq) use ($startDate) {
                      $sq->whereNull('paid_at')->where('created_at', '>=', $startDate);
                  });
            });
        } elseif ($period === 'prev_month') {
            $startDate = $now->copy()->subMonth()->startOfMonth()->startOfDay();
            $endDate = $now->copy()->subMonth()->endOfMonth()->endOfDay();
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('paid_at', [$startDate, $endDate])
                  ->orWhere(function ($sq) use ($startDate, $endDate) {
                      $sq->whereNull('paid_at')->whereBetween('created_at', [$startDate, $endDate]);
                  });
            });
        } elseif ($period === '30d') {
            $startDate = $now->copy()->subDays(29)->startOfDay();
            $query->where(function ($q) use ($startDate) {
                $q->where('paid_at', '>=', $startDate)
                  ->orWhere(function ($sq) use ($startDate) {
                      $sq->whereNull('paid_at')->where('created_at', '>=', $startDate);
                  });
            });
        }

        // 2. Type / Layanan Filter
        $type = $request->query('type');
        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        // 3. Search Filter
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%")
                  ->orWhere('payer_name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhereHas('items', function ($iq) use ($search) {
                      $iq->where('item_name', 'like', "%{$search}%");
                  });
            });
        }

        // 4. Calculate aggregate summary for the current filtered query
        $totalDevShare = (int) (clone $query)->sum('developer_net_share');
        $totalTransactions = (int) (clone $query)->count();

        $payments = $query->latest('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $payments,
            'summary' => [
                'total_dev_share' => $totalDevShare,
                'total_transactions' => $totalTransactions,
                'period' => $period,
            ],
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
