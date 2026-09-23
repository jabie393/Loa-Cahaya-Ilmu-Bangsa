@php
    $amountValue = (float) ($record->amount ?? 0);
    $payload = $amountValue > 0 ? \App\Services\DynamicQrisService::makeDynamic($amountValue) : null;
    $qrSvg = $payload ? \App\Services\DynamicQrisService::renderQrSvg($payload) : null;
    $qrPng = $payload ? \App\Services\DynamicQrisService::renderQrPng($payload) : null;
@endphp

<div class="py-1">
    @if ($qrSvg && $qrPng)
        <div x-data="{
            isDownloading: false,
            downloadQris() {
                this.isDownloading = true;
                const base64Data = @js($qrPng);
                const filename = @js('QRIS-' . $record->payout_no . '.png');

                fetch(base64Data)
                    .then(res => res.blob())
                    .then(blob => {
                        const blobUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = blobUrl;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        setTimeout(() => window.URL.revokeObjectURL(blobUrl), 1000);
                    })
                    .catch(err => {
                        const a = document.createElement('a');
                        a.href = base64Data;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                    })
                    .finally(() => {
                        setTimeout(() => {
                            this.isDownloading = false;
                        }, 600);
                    });
            }
        }" class="grid grid-cols-1 md:grid-cols-12 gap-5 items-center p-4 sm:p-5 bg-gradient-to-br from-slate-50 via-white to-blue-50/30 dark:from-gray-900 dark:via-gray-900 dark:to-gray-800/90 rounded-2xl border border-slate-200/90 dark:border-slate-700/80 shadow-xs">
            
            {{-- LEFT COLUMN: QR CODE & DOWNLOAD ACTION --}}
            <div class="md:col-span-5 flex flex-col items-center justify-center gap-3">
                <div class="p-3 bg-white rounded-2xl border border-slate-200/90 dark:border-slate-700 shadow-sm flex items-center justify-center relative">
                    <img src="{{ $qrSvg }}" 
                         alt="Developer QRIS Payout {{ $record->payout_no }}" 
                         class="w-40 sm:w-44 md:w-48 h-auto object-contain rounded-lg mx-auto" />
                </div>

                {{-- Download QRIS Button --}}
                <button type="button" 
                        @click="downloadQris()" 
                        :disabled="isDownloading"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-white dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-gray-700 text-slate-700 hover:text-primary dark:text-gray-200 dark:hover:text-primary border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold shadow-2xs transition-all active:scale-[0.98] cursor-pointer w-full max-w-[200px]">
                    <template x-if="isDownloading">
                        <svg class="w-3.5 h-3.5 animate-spin text-primary" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <template x-if="!isDownloading">
                        <svg class="w-3.5 h-3.5 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                    </template>
                    <span x-text="isDownloading ? 'Mengunduh...' : 'Download QRIS'"></span>
                </button>
            </div>

            {{-- RIGHT COLUMN: DEVELOPER INFO, LOCKED NOMINAL & PAYMENT GUIDE --}}
            <div class="md:col-span-7 flex flex-col justify-between h-full space-y-3.5 text-left">
                
                {{-- Header / Merchant Info --}}
                <div class="space-y-1 text-center md:text-left">
                    <div class="flex flex-col md:flex-row items-center justify-center md:justify-start gap-1 md:gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold tracking-wider bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300 border border-blue-300/60 shadow-2xs">
                            DEVELOPER QRIS RESMI
                        </span>
                        <span class="text-xs font-mono text-slate-500 dark:text-slate-400 font-medium">
                            {{ $record->payout_no }}
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight pt-0.5">
                        {{ \App\Services\DynamicQrisService::MERCHANT_NAME }}
                    </h3>
                    <p class="text-xs font-mono text-slate-500 dark:text-slate-400">
                        NMID: <span class="font-medium text-slate-700 dark:text-slate-300">{{ \App\Services\DynamicQrisService::NMID }}</span>
                    </p>
                </div>

                {{-- Locked Amount Box (Theme Blue Accent) --}}
                <div class="p-3.5 rounded-2xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200/90 dark:border-blue-800/60 text-center md:text-left shadow-2xs">
                    <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider block">
                        Nominal Transfer (Terkunci)
                    </span>
                    <div class="text-2xl sm:text-3xl font-black font-mono tracking-tight text-blue-600 dark:text-blue-400 mt-0.5">
                        Rp {{ number_format($amountValue, 0, ',', '.') }}
                    </div>
                </div>

                {{-- Payment Instructions Note --}}
                <div class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-100/80 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    <x-filament::icon icon="heroicon-o-information-circle" class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" />
                    <p class="text-[11px] sm:text-xs">
                        Nominal pembayaran otomatis terisi saat di-scan melalui <strong>Mobile Banking</strong> (BCA, Mandiri, BRI, BNI, BSI) atau <strong>E-Wallet</strong> (GoPay, OVO, Dana, ShopeePay).
                    </p>
                </div>

            </div>
        </div>
    @else
        <div class="py-6 text-center text-sm text-red-500 font-semibold">
            Nominal payout tidak valid untuk membuat QRIS Dinamis.
        </div>
    @endif
</div>
