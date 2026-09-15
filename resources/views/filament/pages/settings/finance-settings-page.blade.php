<x-filament-panels::page>
    <div class="space-y-6">

        {{-- TOP STAT CARDS: FINANCIAL OVERVIEW --}}
        @php
            $totalGross = \App\Models\Payment::where('payment_status', 'paid')->sum('gross_amount');
            $totalQris = \App\Models\Payment::where('payment_status', 'paid')->sum('mdr_amount');
            $totalDev = \App\Models\Payment::where('payment_status', 'paid')->sum('developer_net_share');
            $totalAdmin = \App\Models\Payment::where('payment_status', 'paid')->sum('journal_share');
            $countPayments = \App\Models\Payment::where('payment_status', 'paid')->count();
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5">
            {{-- GROSS REVENUE --}}
            <div class="bg-white dark:bg-gray-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:border-slate-300 dark:hover:border-slate-700 transition-all flex flex-col justify-between h-full">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 min-w-0 flex-1 leading-snug">
                        Total Omset Kotor
                    </span>
                    <span class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 shrink-0">
                        <x-filament::icon icon="heroicon-o-currency-dollar" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-4 pt-1">
                    <div class="text-2xl font-black font-mono tracking-tight text-slate-900 dark:text-white">
                        Rp {{ number_format($totalGross, 0, ',', '.') }}
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 flex items-center gap-1.5 leading-relaxed">
                        <span>{{ $countPayments }} Transaksi QRIS Lunas</span>
                    </p>
                </div>
            </div>

            {{-- QRIS GATEWAY FEE --}}
            <div class="bg-white dark:bg-gray-900 border border-amber-200/80 dark:border-amber-800/60 rounded-2xl p-5 shadow-xs hover:border-amber-300 dark:hover:border-amber-700 transition-all flex flex-col justify-between h-full">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 min-w-0 flex-1 leading-snug">
                        Potongan QRIS (0.7%)
                    </span>
                    <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 shrink-0">
                        <x-filament::icon icon="heroicon-o-receipt-percent" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-4 pt-1">
                    <div class="text-2xl font-black font-mono tracking-tight text-amber-700 dark:text-amber-300">
                        Rp {{ number_format($totalQris, 0, ',', '.') }}
                    </div>
                    <p class="text-xs text-amber-600/80 dark:text-amber-400/80 mt-1.5 leading-relaxed">
                        Biaya MDR Gateway Otomatis
                    </p>
                </div>
            </div>

            {{-- DEV CUT (EMERALD GRADIENT) --}}
            <div class="bg-white dark:bg-gray-900 border border-emerald-200/80 dark:border-emerald-800/60 rounded-2xl p-5 shadow-xs hover:border-emerald-300 dark:hover:border-emerald-700 transition-all flex flex-col justify-between h-full">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 min-w-0 flex-1 leading-snug">
                        Hak Bersih Dev
                    </span>
                    <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 shrink-0">
                        <x-filament::icon icon="heroicon-o-code-bracket" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-4 pt-1">
                    <div class="text-2xl font-black font-mono tracking-tight text-emerald-700 dark:text-emerald-300">
                        Rp {{ number_format($totalDev, 0, ',', '.') }}
                    </div>
                    <div class="mt-1.5 flex items-center justify-between text-xs text-emerald-700/80 dark:text-emerald-400/80 leading-relaxed">
                        <span>Porsi Otomatis Dev</span>
                        <span class="font-bold">Sisa: Rp {{ number_format($devUnpaidBalance, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- ADMIN CUT (BLUE GRADIENT) --}}
            <div class="bg-white dark:bg-gray-900 border border-blue-200/80 dark:border-blue-800/60 rounded-2xl p-5 shadow-xs hover:border-blue-300 dark:hover:border-blue-700 transition-all flex flex-col justify-between h-full">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400 min-w-0 flex-1 leading-snug">
                        Hak Bersih Admin
                    </span>
                    <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 shrink-0">
                        <x-filament::icon icon="heroicon-o-building-office-2" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-4 pt-1">
                    <div class="text-2xl font-black font-mono tracking-tight text-blue-700 dark:text-blue-300">
                        Rp {{ number_format($totalAdmin, 0, ',', '.') }}
                    </div>
                    <p class="text-xs text-blue-700/80 dark:text-blue-400/80 mt-1.5 leading-relaxed">
                        Porsi Pengelola Jurnal CIB
                    </p>
                </div>
            </div>
        </div>

        {{-- NAVIGATION TABS --}}
        <div class="border-b border-gray-200 dark:border-gray-800 flex items-center gap-6 text-sm font-semibold overflow-x-auto">
            <button wire:click="$set('activeTab', 'transactions')" 
                    class="pb-3 border-b-2 transition-all flex items-center gap-2 whitespace-nowrap {{ $activeTab === 'transactions' ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-800 dark:hover:text-gray-300' }}">
                <x-filament::icon icon="heroicon-o-table-cells" class="w-4 h-4" />
                <span>1. Transaksi Naskah & Potongan</span>
            </button>

            <button wire:click="$set('activeTab', 'payouts')" 
                    class="pb-3 border-b-2 transition-all flex items-center gap-2 whitespace-nowrap {{ $activeTab === 'payouts' ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-800 dark:hover:text-gray-300' }}">
                <x-filament::icon icon="heroicon-o-banknotes" class="w-4 h-4 text-emerald-600" />
                <span>2. Bayar Developer (Payout Hub)</span>
                @if($devUnpaidBalance > 0)
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 animate-pulse">
                        Siap Cair
                    </span>
                @endif
            </button>
        </div>

        {{-- TAB 1: FILAMENT NATIVE TABLE TRANSAKSI --}}
        @if($activeTab === 'transactions')
            <div class="space-y-4">
                {{ $this->table }}
            </div>
        @endif

        {{-- TAB 2: FILAMENT NATIVE TABLE BAYAR DEVELOPER --}}
        @if($activeTab === 'payouts')
            <div class="space-y-6">
                
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

                {{-- NATIVE FILAMENT TABLE FOR PAYOUTS & PAY THE DEV BUTTON --}}
                <div class="space-y-4">
                    @livewire(\App\Livewire\DevPayoutsTable::class)
                </div>

            </div>
        @endif

    </div>
</x-filament-panels::page>
