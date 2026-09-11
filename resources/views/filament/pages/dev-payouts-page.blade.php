<x-filament-panels::page>
    <div class="space-y-6">
        @php
            $devTotalEarned = (float) \App\Models\Payment::where('payment_status', 'paid')->sum('developer_net_share');
            $devTotalPaid = (float) \App\Models\DevPayout::whereIn('status', ['waiting_confirmation', 'confirmed', 'completed'])->sum('amount');
            $devTotalCommitted = (float) \App\Models\DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation', 'confirmed', 'completed'])->sum('amount');
            $devUnpaidBalance = max(0, $devTotalEarned - $devTotalCommitted);
            $unpaidPayoutCount = \App\Models\DevPayout::where('status', 'waiting_payout')->count();
            $unpaidPayoutTotal = (float) \App\Models\DevPayout::where('status', 'waiting_payout')->sum('amount');
        @endphp

        {{-- DEVELOPER BALANCE WIDGET CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Hak Dev Terkumpul</span>
                    <span class="p-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                        <x-filament::icon icon="heroicon-o-chart-bar" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black font-mono text-gray-900 dark:text-white">
                        Rp {{ number_format($devTotalEarned, 0, ',', '.') }}
                    </div>
                    <span class="text-xs text-gray-400 mt-1 block">Akumulasi seluruh transaksi lunas</span>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Sudah Ditransfer ke Dev</span>
                    <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400">
                        <x-filament::icon icon="heroicon-o-check-badge" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black font-mono text-blue-700 dark:text-blue-300">
                        Rp {{ number_format($devTotalPaid, 0, ',', '.') }}
                    </div>
                    <span class="text-xs text-blue-600/70 dark:text-blue-400/70 mt-1 block">{{ \App\Models\DevPayout::whereIn('status', ['waiting_confirmation', 'confirmed', 'completed'])->count() }} Kali Pencairan Berhasil</span>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-amber-200/80 dark:border-amber-800/60 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Menunggu Payout</span>
                    <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400">
                        <x-filament::icon icon="heroicon-o-clock" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black font-mono text-amber-600 dark:text-amber-400">
                        {{ $unpaidPayoutCount }} Tagihan
                    </div>
                    <span class="text-xs text-amber-600/80 dark:text-amber-400/80 mt-1 block">
                        Rp {{ number_format($unpaidPayoutTotal, 0, ',', '.') }} siap dibayar via QRIS
                    </span>
                </div>
            </div>

            <div class="bg-gradient-to-br from-blue-600 to-indigo-800 text-white rounded-2xl p-5 shadow-lg relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-200">Sisa Hak Dev Belum Cair</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-400/20 border border-blue-300/30 text-blue-100">
                        Siap Dicairkan
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-3xl font-black font-mono tracking-tight">
                        Rp {{ number_format($devUnpaidBalance, 0, ',', '.') }}
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs text-blue-100/90">
                        <span>Saldo outstanding yang dapat dibayarkan</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- PAYOUTS TABLE --}}
        <div class="space-y-4">
            @livewire(\App\Livewire\DevPayoutsTable::class)
        </div>
    </div>
</x-filament-panels::page>
