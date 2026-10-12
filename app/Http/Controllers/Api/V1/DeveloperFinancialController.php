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

        // 2. Financial Metrics Calculation (Synchronized with strict developer confirmation lifecycle)
        $totalEarned = (int) Payment::where('payment_status', 'paid')->sum('developer_net_share');
        $totalTransferred = (int) DevPayout::whereIn('status', ['confirmed', 'completed'])->sum('amount');
        $totalCommitted = (int) DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation', 'confirmed', 'completed', 'rejected'])->sum('amount');

        $pendingPayout = (int) DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation'])->sum('amount');
        $unpaidPayoutCount = (int) DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation'])->count();
        $waitingPayoutCount = (int) DevPayout::where('status', 'waiting_payout')->count();
        $waitingConfirmationCount = (int) DevPayout::where('status', 'waiting_confirmation')->count();
        $unpaidBalance = max(0, $totalEarned - $totalCommitted);

        // 3. Payout Statistics
        $payoutSuccessful = (int) DevPayout::whereIn('status', ['confirmed', 'completed'])->count();
        $payoutPending = (int) DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation'])->count();
        $payoutFailed = (int) DevPayout::where('status', 'rejected')->count();

        // 4. Trend Chart Calculation (Identical to DevPayoutsChartWidget.php)
        $filter = $request->query('period', '7d');
        $now = Carbon::now();
        $labels = [];
        $values = [];

        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();
        $hasConfirmedPayoutToday = DevPayout::query()
            ->whereIn('status', ['confirmed', 'completed'])
            ->where(function ($q) use ($todayStart, $todayEnd) {
                $q->whereBetween('updated_at', [$todayStart, $todayEnd])
                  ->orWhere(function ($q2) use ($todayStart, $todayEnd) {
                      $q2->whereNull('updated_at')
                         ->whereBetween('created_at', [$todayStart, $todayEnd]);
                  });
            })
            ->where('amount', '>', 0)
            ->exists();

        if ($filter === 'year') {
            $startDate = $now->copy()->startOfYear()->startOfDay();
            $endDate = $now->copy()->endOfYear()->endOfDay();

            $payouts = DevPayout::query()
                ->whereIn('status', ['confirmed', 'completed'])
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('updated_at', [$startDate, $endDate])
                      ->orWhere(function ($q2) use ($startDate, $endDate) {
                          $q2->whereNull('updated_at')
                             ->whereBetween('created_at', [$startDate, $endDate]);
                      });
                })
                ->get(['amount', 'updated_at', 'created_at'])
                ->groupBy(fn($item) => ($item->updated_at ?? $item->created_at)->format('Y-m'))
                ->map(fn($group) => (float) $group->sum('amount'));

            $monthNames = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
                9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
            ];
            for ($m = 1; $m <= 12; $m++) {
                $monthDate = Carbon::create($now->year, $m, 1);
                $key = $monthDate->format('Y-m');
                $labels[] = $monthNames[$m];
                $values[] = (float) ($payouts->get($key) ?? 0);
            }
        } else {
            if ($filter === '7d') {
                if ($hasConfirmedPayoutToday) {
                    $startDate = $now->copy()->subDays(6)->startOfDay();
                    $endDate = $now->copy()->endOfDay();
                } else {
                    $startDate = $now->copy()->subDays(7)->startOfDay();
                    $endDate = $now->copy()->subDays(1)->endOfDay();
                }
                $period = CarbonPeriod::create($startDate, '1 day', $endDate);
            } elseif ($filter === 'month') {
                $startDate = $now->copy()->startOfMonth()->startOfDay();
                $endDate = $hasConfirmedPayoutToday ? $now->copy()->endOfDay() : $now->copy()->subDays(1)->endOfDay();
                if ($endDate < $startDate) {
                    $endDate = $now->copy()->endOfDay();
                }
                $period = CarbonPeriod::create($startDate, '1 day', $endDate);
            } else { // default '30d'
                if ($hasConfirmedPayoutToday) {
                    $startDate = $now->copy()->subDays(29)->startOfDay();
                    $endDate = $now->copy()->endOfDay();
                } else {
                    $startDate = $now->copy()->subDays(30)->startOfDay();
                    $endDate = $now->copy()->subDays(1)->endOfDay();
                }
                $period = CarbonPeriod::create($startDate, '1 day', $endDate);
            }

            $payouts = DevPayout::query()
                ->whereIn('status', ['confirmed', 'completed'])
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('updated_at', [$startDate, $endDate])
                      ->orWhere(function ($q2) use ($startDate, $endDate) {
                          $q2->whereNull('updated_at')
                             ->whereBetween('created_at', [$startDate, $endDate]);
                      });
                })
                ->get(['amount', 'updated_at', 'created_at'])
                ->groupBy(fn($item) => ($item->updated_at ?? $item->created_at)->format('Y-m-d'))
                ->map(fn($group) => (float) $group->sum('amount'));

            foreach ($period as $date) {
                if ($date->isFuture()) {
                    continue;
                }

                $key = $date->format('Y-m-d');
                $labels[] = $date->format('d M');
                $values[] = (float) ($payouts->get($key) ?? 0);
            }
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
                    'waiting_payout_count' => $waitingPayoutCount,
                    'waiting_confirmation_count' => $waitingConfirmationCount,
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
            if ($type === 'replace_pdf' || $type === 'ganti_pdf') {
                $query->where(function ($q) {
                    $q->whereIn('type', ['replace_pdf', 'ganti_pdf'])
                      ->orWhere('type', 'like', '%pdf%')
                      ->orWhereHas('items', function ($iq) {
                          $iq->where('item_name', 'like', '%pdf%')
                            ->orWhere('item_type', 'like', '%pdf%');
                      });
                });
            } elseif ($type === 'doi_addon' || $type === 'doi') {
                $query->where(function ($q) {
                    $q->whereIn('type', ['doi_addon', 'doi'])
                      ->orWhereHas('items', function ($iq) {
                          $iq->where('item_type', 'doi_addon');
                      });
                });
            } elseif ($type === 'bulk_submission' || $type === 'bulk') {
                $query->where(function ($q) {
                    $q->where('type', 'bulk_submission')
                      ->orWhere('order_id', 'like', '%BULK%')
                      ->orWhere('invoice_number', 'like', '%BULK%');
                });
            } else {
                $query->where('type', $type);
            }
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
     * Get paginated dev payout batches and records with filtering and aggregates.
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

        $query = DevPayout::query();

        // 1. Status filter
        $status = $request->query('status');
        if ($status && $status !== 'all') {
            if ($status === 'completed' || $status === 'confirmed') {
                $query->whereIn('status', ['confirmed', 'completed']);
            } elseif ($status === 'pending') {
                $query->whereNotIn('status', ['confirmed', 'completed']);
            } else {
                $query->where('status', $status);
            }
        }

        // 2. Period filter
        $period = $request->query('period', 'all');
        $now = Carbon::now();
        if ($period === 'today') {
            $startDate = $now->copy()->startOfDay();
            $endDate = $now->copy()->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($period === '7d') {
            $startDate = $now->copy()->subDays(6)->startOfDay();
            $query->where('created_at', '>=', $startDate);
        } elseif ($period === '30d') {
            $startDate = $now->copy()->subDays(29)->startOfDay();
            $query->where('created_at', '>=', $startDate);
        } elseif ($period === 'month') {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($period === 'prev_month') {
            $startDate = $now->copy()->subMonthNoOverflow()->startOfMonth();
            $endDate = $now->copy()->subMonthNoOverflow()->endOfMonth();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($period === 'year') {
            $targetYear = (int) $request->query('year', $now->year);
            $query->whereYear('created_at', $targetYear);
        } elseif ($period === 'month_year') {
            $targetYear = (int) $request->query('year', $now->year);
            $targetMonth = (int) $request->query('month', $now->month);
            $query->whereYear('created_at', $targetYear)->whereMonth('created_at', $targetMonth);
        }

        // 3. Search filter
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('payout_no', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        // 4. Calculate aggregates for this filter
        $completedSum = (int) (clone $query)->whereIn('status', ['confirmed', 'completed'])->sum('amount');
        $pendingSum = (int) (clone $query)->whereNotIn('status', ['confirmed', 'completed'])->sum('amount');
        $completedCount = (int) (clone $query)->whereIn('status', ['confirmed', 'completed'])->count();
        $totalCount = (int) (clone $query)->count();

        $payouts = $query->latest('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $payouts,
            'summary' => [
                'total_completed_amount' => $completedSum,
                'total_pending_amount' => $pendingSum,
                'completed_count' => $completedCount,
                'total_payouts' => $totalCount,
            ],
        ]);
    }

    /**
     * Confirm payout receipt by developer.
     */
    public function confirmPayout(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('ryu_dev')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], 403);
        }

        $payout = DevPayout::find($id);
        if (!$payout) {
            return response()->json([
                'success' => false,
                'message' => 'Data payout tidak ditemukan.',
            ], 404);
        }

        $payout->update(['status' => 'confirmed']);

        return response()->json([
            'success' => true,
            'message' => "Payout {$payout->payout_no} telah berhasil Anda konfirmasi sebagai dana masuk.",
            'data' => $payout,
        ]);
    }

    /**
     * Reject payout / report issue by developer.
     */
    public function rejectPayout(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('ryu_dev')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], 403);
        }

        $reason = trim((string) $request->input('rejection_reason', ''));
        if ($reason === '') {
            return response()->json([
                'success' => false,
                'message' => 'Alasan penolakan / masalah wajib diisi.',
            ], 422);
        }

        $payout = DevPayout::find($id);
        if (!$payout) {
            return response()->json([
                'success' => false,
                'message' => 'Data payout tidak ditemukan.',
            ], 404);
        }

        $payout->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Payout {$payout->payout_no} ditandai sebagai belum diterima / ditolak.",
            'data' => $payout,
        ]);
    }

    /**
     * Store or update device FCM token for push notifications and widget synchronization.
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        // Pastikan hanya role ryu_dev yang dapat mendaftarkan notifikasi
        if (!$user->hasRole('ryu_dev')) {
            $user->update(['fcm_token' => null]);
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Fitur notifikasi hanya untuk role developer (ryu_dev).',
            ], 403);
        }

        $validated = $request->validate([
            'fcm_token' => 'nullable|string',
        ]);

        $tokenVal = !empty($validated['fcm_token']) ? $validated['fcm_token'] : null;

        $user->update([
            'fcm_token' => $tokenVal,
        ]);

        return response()->json([
            'success' => true,
            'message' => $tokenVal ? 'FCM Token berhasil diperbarui.' : 'FCM Token berhasil dihapus.',
        ]);
    }
}
