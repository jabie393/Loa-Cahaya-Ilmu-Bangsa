<x-filament-panels::page>
    <div class="space-y-6">
        @php
            $devTotalEarned = (float) \App\Models\Payment::where('payment_status', 'paid')->sum('developer_net_share');
            $devTotalPaid = (float) \App\Models\DevPayout::sum('amount');
            $devUnpaidBalance = max(0, $devTotalEarned - $devTotalPaid);
        @endphp

        {{-- DEVELOPER BALANCE CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
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
                    <span class="text-xs text-blue-600/70 dark:text-blue-400/70 mt-1 block">{{ \App\Models\DevPayout::count() }} Kali Pencairan Berhasil</span>
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

        {{-- PAYOUTS TABLE --}}
        <div class="space-y-4">
            @livewire(\App\Livewire\DevPayoutsTable::class)
        </div>
    </div>
</x-filament-panels::page>
