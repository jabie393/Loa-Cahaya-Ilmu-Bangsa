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
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5">
            {{-- CARD 1: TOTAL TERKUMPUL --}}
            <div class="bg-white dark:bg-gray-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:border-slate-300 dark:hover:border-slate-700 transition-all flex flex-col justify-between h-full">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 min-w-0 flex-1 leading-snug">
                        Total Hak Dev Terkumpul
                    </span>
                    <span class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 shrink-0">
                        <x-filament::icon icon="heroicon-o-chart-bar" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-4 pt-1">
                    <div class="text-2xl font-black font-mono tracking-tight text-slate-900 dark:text-white">
                        Rp {{ number_format($devTotalEarned, 0, ',', '.') }}
                    </div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 block leading-relaxed">
                        Akumulasi seluruh transaksi lunas
                    </span>
                </div>
            </div>

            {{-- CARD 2: SUDAH DITRANSFER (SUCCESS / EMERALD) --}}
            <div class="bg-white dark:bg-gray-900 border border-emerald-200/80 dark:border-emerald-800/60 rounded-2xl p-5 shadow-xs hover:border-emerald-300 dark:hover:border-emerald-700 transition-all flex flex-col justify-between h-full">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 min-w-0 flex-1 leading-snug">
                        Sudah Ditransfer ke Dev
                    </span>
                    <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 shrink-0">
                        <x-filament::icon icon="heroicon-o-check-badge" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-4 pt-1">
                    <div class="text-2xl font-black font-mono tracking-tight text-emerald-700 dark:text-emerald-300">
                        Rp {{ number_format($devTotalPaid, 0, ',', '.') }}
                    </div>
                    <span class="text-xs text-emerald-600/80 dark:text-emerald-400/80 mt-1.5 block leading-relaxed">
                        {{ \App\Models\DevPayout::whereIn('status', ['waiting_confirmation', 'confirmed', 'completed'])->count() }} Kali Pencairan Berhasil
                    </span>
                </div>
            </div>

            {{-- CARD 3: MENUNGGU PAYOUT (WARNING / AMBER) --}}
            <div class="bg-white dark:bg-gray-900 border border-amber-200/80 dark:border-amber-800/60 rounded-2xl p-5 shadow-xs hover:border-amber-300 dark:hover:border-amber-700 transition-all flex flex-col justify-between h-full">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 min-w-0 flex-1 leading-snug">
                        Menunggu Payout
                    </span>
                    <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 shrink-0">
                        <x-filament::icon icon="heroicon-o-clock" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-4 pt-1">
                    <div class="text-2xl font-black font-mono tracking-tight text-amber-700 dark:text-amber-300">
                        {{ $unpaidPayoutCount }} Tagihan
                    </div>
                    <span class="text-xs text-amber-600/80 dark:text-amber-400/80 mt-1.5 block leading-relaxed">
                        Rp {{ number_format($unpaidPayoutTotal, 0, ',', '.') }} siap dibayar via QRIS
                    </span>
                </div>
            </div>

            {{-- CARD 4: SISA HAK DEV (ACTION / ELEGANT INDIGO WITH NON-COLLIDING BADGE) --}}
            <div class="bg-gradient-to-br from-indigo-50/50 via-white to-white dark:from-indigo-950/30 dark:via-gray-900 dark:to-gray-900 border border-indigo-200/80 dark:border-indigo-800/60 rounded-2xl p-5 shadow-xs hover:border-indigo-300 dark:hover:border-indigo-700 transition-all flex flex-col justify-between h-full">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 min-w-0 flex-1 leading-snug">
                        Sisa Hak Dev Belum Cair
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wider shrink-0 whitespace-nowrap {{ $devUnpaidBalance > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800 shadow-xs' : 'bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800/60' }}">
                        @if($devUnpaidBalance > 0)
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        @endif
                        Siap Dicairkan
                    </span>
                </div>
                <div class="mt-4 pt-1">
                    <div class="text-2xl font-black font-mono tracking-tight text-indigo-700 dark:text-indigo-300">
                        Rp {{ number_format($devUnpaidBalance, 0, ',', '.') }}
                    </div>
                    <span class="text-xs text-indigo-600/80 dark:text-indigo-400/80 mt-1.5 block leading-relaxed">
                        Saldo outstanding yang dapat dibayarkan
                    </span>
                </div>
            </div>
        </div>

        {{-- PAYOUTS TABLE --}}
        <div class="space-y-4">
            @livewire(\App\Livewire\DevPayoutsTable::class)
        </div>
    </div>
</x-filament-panels::page>
