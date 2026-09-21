<x-filament-panels::page>
    <div class="space-y-6" wire:poll.5s="refreshBalances">

        {{-- DEVELOPER PAYOUTS AMOUNT CHART (FULL WIDTH - RYU_DEV ONLY) --}}
        @if (auth()->user()?->hasRole('ryu_dev'))
            <div class="w-full min-w-0 overflow-hidden [&_.fi-section]:rounded-2xl [&_.fi-section]:border [&_.fi-section]:border-slate-200/80 [&_.fi-section]:shadow-xs dark:[&_.fi-section]:border-slate-800 dark:[&_.fi-section]:bg-gray-900">
                @livewire(\App\Filament\Widgets\DevPayoutsChartWidget::class)
            </div>
        @endif

        {{-- DEVELOPER BALANCE WIDGET CARDS --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 xl:grid-cols-4">
            {{-- CARD 1: TOTAL TERKUMPUL --}}
            <div
                class="shadow-xs flex h-full flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-5 transition-all hover:border-slate-300 dark:border-slate-800 dark:bg-gray-900 dark:hover:border-slate-700">
                <div class="flex items-start justify-between gap-3">
                    <span class="min-w-0 flex-1 text-xs font-bold uppercase leading-snug tracking-wider text-slate-500 dark:text-slate-400">
                        Total Hak Dev Terkumpul
                    </span>
                    <span class="shrink-0 rounded-xl bg-slate-100 p-2 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        <x-filament::icon icon="heroicon-o-chart-bar" class="h-5 w-5" />
                    </span>
                </div>
                <div class="mt-4 pt-1">
                    <div class="font-mono text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                        Rp {{ number_format($devTotalEarned, 0, ',', '.') }}
                    </div>
                    <span class="mt-1.5 block text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                        Akumulasi seluruh transaksi lunas
                    </span>
                </div>
            </div>

            {{-- CARD 2: SUDAH DITRANSFER (SUCCESS / EMERALD) --}}
            <div
                class="shadow-xs flex h-full flex-col justify-between rounded-2xl border border-emerald-200/80 bg-white p-5 transition-all hover:border-emerald-300 dark:border-emerald-800/60 dark:bg-gray-900 dark:hover:border-emerald-700">
                <div class="flex items-start justify-between gap-3">
                    <span class="min-w-0 flex-1 text-xs font-bold uppercase leading-snug tracking-wider text-emerald-700 dark:text-emerald-400">
                        Sudah Ditransfer ke Dev
                    </span>
                    <span class="shrink-0 rounded-xl bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">
                        <x-filament::icon icon="heroicon-o-check-badge" class="h-5 w-5" />
                    </span>
                </div>
                <div class="mt-4 pt-1">
                    <div class="font-mono text-2xl font-black tracking-tight text-emerald-700 dark:text-emerald-300">
                        Rp {{ number_format($devTotalPaid, 0, ',', '.') }}
                    </div>
                    <span class="mt-1.5 block text-xs leading-relaxed text-emerald-600/80 dark:text-emerald-400/80">
                        {{ \App\Models\DevPayout::whereIn('status', ['waiting_confirmation', 'confirmed', 'completed'])->count() }} Kali Pencairan Berhasil
                    </span>
                </div>
            </div>

            {{-- CARD 3: MENUNGGU PAYOUT (WARNING / AMBER) --}}
            <div
                class="shadow-xs flex h-full flex-col justify-between rounded-2xl border border-amber-200/80 bg-white p-5 transition-all hover:border-amber-300 dark:border-amber-800/60 dark:bg-gray-900 dark:hover:border-amber-700">
                <div class="flex items-start justify-between gap-3">
                    <span class="min-w-0 flex-1 text-xs font-bold uppercase leading-snug tracking-wider text-amber-700 dark:text-amber-400">
                        Menunggu Payout
                    </span>
                    <span class="shrink-0 rounded-xl bg-amber-50 p-2 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400">
                        <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5" />
                    </span>
                </div>
                <div class="mt-4 pt-1">
                    <div class="font-mono text-2xl font-black tracking-tight text-amber-700 dark:text-amber-300">
                        {{ $unpaidPayoutCount }} Tagihan
                    </div>
                    <span class="mt-1.5 block text-xs leading-relaxed text-amber-600/80 dark:text-amber-400/80">
                        Rp {{ number_format($unpaidPayoutTotal, 0, ',', '.') }} siap dibayar via QRIS
                    </span>
                </div>
            </div>

            {{-- CARD 4: HAK DEV (ACTION / ELEGANT INDIGO FULLY UNIFORM) --}}
            <div
                class="shadow-xs flex h-full flex-col justify-between rounded-2xl border border-indigo-200/80 bg-gradient-to-br from-indigo-50/50 via-white to-white p-5 transition-all hover:border-indigo-300 dark:border-indigo-800/60 dark:from-indigo-950/30 dark:via-gray-900 dark:to-gray-900 dark:hover:border-indigo-700">
                <div class="flex items-start justify-between gap-3">
                    <span class="min-w-0 flex-1 text-xs font-bold uppercase leading-snug tracking-wider text-indigo-700 dark:text-indigo-400">
                        Hak Dev Hari ini
                    </span>
                    <span class="shrink-0 rounded-xl bg-indigo-50 p-2 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                        <x-filament::icon icon="heroicon-o-banknotes" class="h-5 w-5" />
                    </span>
                </div>
                <div class="mt-4 pt-1">
                    <div class="font-mono text-2xl font-black tracking-tight text-indigo-700 dark:text-indigo-300">
                        Rp {{ number_format($devUnpaidBalance, 0, ',', '.') }}
                    </div>
                    <div class="mt-1.5 flex items-center justify-between gap-2 text-xs">
                        <span class="truncate text-indigo-600/80 dark:text-indigo-400/80">Saldo siap bayar</span>
                        @if ($devUnpaidBalance > 0)
                            <span
                                class="inline-flex shrink-0 items-center gap-1 whitespace-nowrap rounded-full border border-emerald-200/80 bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800 dark:border-emerald-800/80 dark:bg-emerald-900/60 dark:text-emerald-200">
                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>
                                Siap Cair
                            </span>
                        @else
                            <span class="whitespace-nowrap text-xs font-semibold text-indigo-600/80 dark:text-indigo-400/80">Lunas</span>
                        @endif
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
