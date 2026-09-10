@php
    $amountValue = (float) ($amount ?? 0);
    $hasAmount = $amountValue > 0;
    $payload = $hasAmount ? \App\Services\DynamicQrisService::makeDynamic($amountValue) : null;
    $qrSvg = $payload ? \App\Services\DynamicQrisService::renderQrSvg($payload) : null;
@endphp

<div class="flex flex-col items-center justify-center p-4 bg-gradient-to-b from-gray-50 to-gray-100/60 dark:from-gray-800/80 dark:to-gray-900/60 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm text-center">
    <!-- Merchant Info Header -->
    <div class="flex flex-col items-center gap-1 mb-2">
        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold tracking-wide bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-300/60">
            DEVELOPER QRIS
        </span>
        <h4 class="text-sm font-bold text-gray-900 dark:text-white tracking-tight mt-1">
            {{ \App\Services\DynamicQrisService::MERCHANT_NAME }}
        </h4>
        <span class="text-[11px] font-mono text-gray-500 dark:text-gray-400">
            NMID: {{ \App\Services\DynamicQrisService::NMID }}
        </span>
    </div>

    @if ($hasAmount && $qrSvg)
        <!-- Dynamic QR Code Container -->
        <div class="p-3 bg-white rounded-xl border border-gray-200 dark:border-gray-700 shadow-inner my-2">
            <img src="{{ $qrSvg }}" 
                 alt="Developer QRIS Payout" 
                 class="w-48 sm:w-56 h-auto object-contain rounded-lg mx-auto" />
        </div>

        <!-- Formatted Amount Badge -->
        <div class="mt-2 mb-1 px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 text-sm font-bold">
            Rp {{ number_format($amountValue, 0, ',', '.') }}
        </div>

        <p class="text-[11px] text-gray-500 dark:text-gray-400 max-w-xs mt-1">
            Nominal otomatis terisi saat di-scan melalui BCA, Mandiri, GoPay, OVO, Dana, ShopeePay, dll.
        </p>

        <!-- Quick Copy QR Payload -->
        <div x-data="{ copied: false }" class="mt-2.5">
            <button type="button" 
                    @click="navigator.clipboard.writeText('{{ $payload }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md border border-gray-300 dark:border-gray-600 transition-colors shadow-xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span x-text="copied ? 'Tersalin ke Clipboard!' : 'Salin Kode QRIS'">Salin Kode QRIS</span>
            </button>
        </div>
    @else
        <!-- Empty / Prompt State -->
        <div class="flex flex-col items-center justify-center p-8 my-3 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl bg-white/50 dark:bg-gray-900/40 max-w-sm">
            <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-950/60 flex items-center justify-center text-amber-600 dark:text-amber-400 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
            </div>
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                Menunggu Input Nominal
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-xs text-center">
                Silakan masukkan nominal pembayaran pada input harga di bawah ini untuk menghasilkan QRIS dinamis secara otomatis.
            </p>
        </div>
    @endif
</div>
