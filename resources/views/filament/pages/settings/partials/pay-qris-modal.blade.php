@php
    $amountValue = (float) ($record->amount ?? 0);
    $payload = $amountValue > 0 ? \App\Services\DynamicQrisService::makeDynamic($amountValue) : null;
    $qrSvg = $payload ? \App\Services\DynamicQrisService::renderQrSvg($payload) : null;
@endphp

<div>
    <div class="flex flex-col items-center justify-center p-4 sm:p-6 bg-gradient-to-b from-gray-50 to-gray-100/70 dark:from-gray-800/90 dark:to-gray-900/80 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm text-center space-y-4">
        <!-- Merchant Header -->
        <div class="flex flex-col items-center gap-1">
            <span class="px-3 py-0.5 rounded-full text-[10px] font-extrabold tracking-wider bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-300/60 shadow-xs">
                DEVELOPER QRIS RESMI
            </span>
            <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white tracking-tight mt-0.5">
                {{ \App\Services\DynamicQrisService::MERCHANT_NAME }}
            </h3>
            <p class="text-xs font-mono text-gray-500 dark:text-gray-400">
                NMID: {{ \App\Services\DynamicQrisService::NMID }} &bull; Payout: <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $record->payout_no }}</span>
            </p>
        </div>

        @if ($qrSvg)
            <!-- Dynamic QR Code Container -->
            <div class="p-4 bg-white rounded-2xl border border-gray-200 dark:border-gray-700 shadow-md flex items-center justify-center">
                <img src="{{ $qrSvg }}" 
                     alt="Developer QRIS Payout {{ $record->payout_no }}" 
                     class="w-44 sm:w-52 h-auto object-contain rounded-lg mx-auto" />
            </div>

            <!-- Read-only Locked Amount Display -->
            <div class="w-full max-w-sm">
                <div class="flex flex-col items-center justify-center py-2.5 px-4 rounded-xl bg-indigo-50/80 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 text-indigo-900 dark:text-indigo-200 shadow-xs">
                    <span class="text-[10.5px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">
                        Nominal Transfer (Terkunci)
                    </span>
                    <span class="text-2xl sm:text-3xl font-black font-mono tracking-tight text-indigo-700 dark:text-indigo-300 mt-0.5">
                        Rp {{ number_format($amountValue, 0, ',', '.') }}
                    </span>
                </div>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                    Nominal pembayaran otomatis terisi saat di-scan melalui Mobile Banking (BCA, Mandiri, BRI, BNI, BSI) atau E-Wallet (GoPay, OVO, Dana, ShopeePay).
                </p>
            </div>
        @else
            <div class="py-6 text-sm text-red-500 font-semibold">
                Nominal payout tidak valid untuk membuat QRIS Dinamis.
            </div>
        @endif
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(100, 116, 139, 0.2);
        border-radius: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(100, 116, 139, 0.4);
    }
</style>
