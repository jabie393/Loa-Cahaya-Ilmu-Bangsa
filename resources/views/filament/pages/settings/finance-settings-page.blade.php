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

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- GROSS REVENUE --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Omset Kotor</span>
                    <span class="p-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                        <x-filament::icon icon="heroicon-o-currency-dollar" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black font-mono tracking-tight text-gray-900 dark:text-white">
                        Rp {{ number_format($totalGross, 0, ',', '.') }}
                    </div>
                    <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                        <span>{{ $countPayments }} Transaksi QRIS Lunas</span>
                    </p>
                </div>
            </div>

            {{-- QRIS GATEWAY FEE --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-500">Potongan QRIS (0.7%)</span>
                    <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                        <x-filament::icon icon="heroicon-o-receipt-percent" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black font-mono tracking-tight text-amber-600 dark:text-amber-400">
                        Rp {{ number_format($totalQris, 0, ',', '.') }}
                    </div>
                    <p class="text-xs text-amber-600/70 dark:text-amber-400/60 mt-1">
                        Biaya MDR Gateway Otomatis
                    </p>
                </div>
            </div>

            {{-- DEV CUT (EMERALD GRADIENT) --}}
            <div class="bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-transparent border border-emerald-200 dark:border-emerald-800/60 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Hak Bersih Dev</span>
                    <span class="p-2 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300">
                        <x-filament::icon icon="heroicon-o-code-bracket" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black font-mono tracking-tight text-emerald-800 dark:text-emerald-300">
                        Rp {{ number_format($totalDev, 0, ',', '.') }}
                    </div>
                    <div class="mt-1 flex items-center justify-between text-xs text-emerald-700/80 dark:text-emerald-400/80">
                        <span>Porsi Otomatis Dev</span>
                        <span class="font-bold">Sisa: Rp {{ number_format($devUnpaidBalance, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- ADMIN CUT (BLUE GRADIENT) --}}
            <div class="bg-gradient-to-br from-blue-500/10 via-blue-500/5 to-transparent border border-blue-200 dark:border-blue-800/60 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400">Hak Bersih Admin</span>
                    <span class="p-2 rounded-xl bg-blue-100 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300">
                        <x-filament::icon icon="heroicon-o-building-office-2" class="w-5 h-5" />
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black font-mono tracking-tight text-blue-800 dark:text-blue-300">
                        Rp {{ number_format($totalAdmin, 0, ',', '.') }}
                    </div>
                    <p class="text-xs text-blue-700/80 dark:text-blue-400/80 mt-1">
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
                
                {{-- DEVELOPER BALANCE CARDS --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Hak Dev Terkumpul</span>
                            <span class="p-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600">
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
                            <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Sudah Ditransfer ke Dev</span>
                            <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950 text-blue-600">
                                <x-filament::icon icon="heroicon-o-check-badge" class="w-5 h-5" />
                            </span>
                        </div>
                        <div class="mt-3">
                            <div class="text-2xl font-black font-mono text-blue-700 dark:text-blue-300">
                                Rp {{ number_format($devTotalPaid, 0, ',', '.') }}
                            </div>
                            <span class="text-xs text-blue-600/70 mt-1 block">{{ \App\Models\DevPayout::count() }} Kali Pencairan Berhasil</span>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-emerald-600 to-teal-800 text-white rounded-2xl p-5 shadow-lg relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-200">Sisa Hak Dev Belum Cair</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-400/20 border border-emerald-300/30 text-emerald-100">
                                Siap Dicairkan
                            </span>
                        </div>
                        <div class="mt-3">
                            <div class="text-3xl font-black font-mono tracking-tight">
                                Rp {{ number_format($devUnpaidBalance, 0, ',', '.') }}
                            </div>
                            <div class="mt-2 flex items-center justify-between text-xs text-emerald-100/90">
                                <span>Saldo outstanding yang dapat dibayarkan</span>
                            </div>
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
