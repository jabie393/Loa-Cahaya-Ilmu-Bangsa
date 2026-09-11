<x-filament-panels::page>
    <div class="space-y-6">

        {{-- PAYMENT GATEWAY SWITCHER BANNER --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-800">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="p-2 rounded-xl bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400">
                            <x-filament::icon icon="heroicon-o-arrows-right-left" class="w-5 h-5" />
                        </span>
                        <div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">Switch Platform Gateway Pembayaran</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pilih penyedia payment gateway yang aktif untuk transaksi QRIS baru.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400">Status Saat Ini:</span>
                    @if($activeGateway === 'belibayar')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/80 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                            Belibayar.id Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Midtrans Aktif
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                {{-- MIDTRANS OPTION --}}
                @php
                    $isMidtransActive = ($activeGateway === 'midtrans');
                    $isMidtransProd = (bool) config('services.midtrans.is_production', false);
                    $hasMidtransKey = !empty(config('services.midtrans.server_key'));
                @endphp
                <div wire:click="switchGateway('midtrans')" 
                     class="cursor-pointer relative rounded-xl p-4 transition-all duration-200 border-2 {{ $isMidtransActive ? 'border-primary-500 bg-primary-50/20 dark:bg-primary-950/20 shadow-sm' : 'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm {{ $isMidtransActive ? 'bg-primary-600 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                MT
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-2">
                                    Midtrans
                                    <span class="text-[10px] uppercase font-mono px-1.5 py-0.5 rounded border {{ $isMidtransProd ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800' : 'bg-gray-100 text-gray-600 border-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700' }}">
                                        {{ $isMidtransProd ? 'Production' : 'Sandbox' }}
                                    </span>
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Core API Snap / QRIS GoPay</p>
                            </div>
                        </div>

                        <div>
                            @if($isMidtransActive)
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-primary-600 dark:text-primary-400">
                                    <x-filament::icon icon="heroicon-s-check-circle" class="w-5 h-5" />
                                    Aktif
                                </span>
                            @else
                                <span class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 font-medium">
                                    Klik untuk Aktifkan
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs text-gray-500">
                        <span>Kredensial Server: {{ $hasMidtransKey ? '✅ Terkonfigurasi' : '⚠️ Belum Ada Key' }}</span>
                        <span class="font-mono text-[11px] text-gray-400">MDR ~0.7%</span>
                    </div>
                </div>

                {{-- BELIBAYAR OPTION --}}
                @php
                    $isBelibayarActive = ($activeGateway === 'belibayar');
                    $isBelibayarProd = (bool) config('services.belibayar.is_production', false);
                    $hasBelibayarKey = !empty(config('services.belibayar.api_key'));
                @endphp
                <div wire:click="switchGateway('belibayar')" 
                     class="cursor-pointer relative rounded-xl p-4 transition-all duration-200 border-2 {{ $isBelibayarActive ? 'border-blue-500 bg-blue-50/20 dark:bg-blue-950/20 shadow-sm' : 'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm {{ $isBelibayarActive ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                BYR
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-2">
                                    Belibayar.id
                                    <span class="text-[10px] uppercase font-mono px-1.5 py-0.5 rounded border {{ $isBelibayarProd ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800' : 'bg-gray-100 text-gray-600 border-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700' }}">
                                        {{ $isBelibayarProd ? 'Production' : 'Sandbox' }}
                                    </span>
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Direct API QRIS Instan</p>
                            </div>
                        </div>

                        <div>
                            @if($isBelibayarActive)
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400">
                                    <x-filament::icon icon="heroicon-s-check-circle" class="w-5 h-5" />
                                    Aktif
                                </span>
                            @else
                                <span class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 font-medium">
                                    Klik untuk Aktifkan
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs text-gray-500">
                        <span>Kredensial API: {{ $hasBelibayarKey ? '✅ Terkonfigurasi' : '⚠️ Masukkan Key di .env' }}</span>
                        <span class="font-mono text-[11px] text-gray-400">Direct QRIS</span>
                    </div>
                </div>
            </div>

            <div class="mt-3 text-[11px] text-gray-400 flex items-center gap-1.5">
                <x-filament::icon icon="heroicon-m-information-circle" class="w-4 h-4 text-gray-400 shrink-0" />
                <span>Pengalihan gateway berlaku seketika untuk tagihan baru. Riwayat pembayaran lama tetap diverifikasi sesuai platform masing-masing tanpa gangguan.</span>
            </div>
        </div>

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

                {{-- NATIVE FILAMENT TABLE FOR PAYOUTS & PAY THE DEV BUTTON --}}
                <div class="space-y-4">
                    @livewire(\App\Livewire\DevPayoutsTable::class)
                </div>

            </div>
        @endif

    </div>
</x-filament-panels::page>
