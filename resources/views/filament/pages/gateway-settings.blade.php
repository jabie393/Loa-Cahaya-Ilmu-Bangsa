<x-filament-panels::page>
    <div class="space-y-5 sm:space-y-6">

        {{-- TOP DEV INFO BANNER --}}
        <div class="bg-gradient-to-r from-slate-900 via-slate-900 to-indigo-950 border border-slate-800 rounded-2xl p-4 sm:p-6 text-white shadow-md relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 text-xs font-semibold mb-2">
                        <x-filament::icon icon="heroicon-m-wrench-screwdriver" class="w-4 h-4 shrink-0" />
                        <span>Developer Console Only</span>
                    </div>
                    <h1 class="text-lg sm:text-xl font-black tracking-tight text-white">Setting Payment Gateway & Developer</h1>
                    <p class="text-xs text-slate-300 mt-1 max-w-xl leading-relaxed">
                        Pengaturan khusus Developer untuk mengalihkan platform payment gateway aktif
                        secara langsung tanpa perlu mengubah berkas <code class="text-indigo-300 bg-indigo-950/60 px-1 py-0.5 rounded font-mono">.env</code>.
                    </p>
                </div>

                <div class="flex items-center justify-start md:justify-end gap-3 shrink-0">
                    <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl px-4 py-2.5 w-full sm:w-auto text-left sm:text-right">
                        <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Gateway Aktif</span>
                        <div class="flex items-center gap-1.5 justify-start sm:justify-end mt-0.5">
                            @if($activeGateway === 'belibayar')
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-400 animate-pulse shrink-0"></span>
                                <span class="font-bold text-sm text-blue-300 font-mono">Belibayar.id</span>
                            @else
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse shrink-0"></span>
                                <span class="font-bold text-sm text-amber-300 font-mono">Midtrans</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PAYMENT GATEWAY SWITCHER CARDS --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 sm:p-6 shadow-sm space-y-4">
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-arrows-right-left" class="w-5 h-5 text-primary-500 shrink-0" />
                    <span>Pilih Platform Payment Gateway Aktif</span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Klik pada kartu gateway di bawah untuk langsung mengaktifkannya bagi seluruh transaksi QRIS baru.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5 pt-1">
                {{-- MIDTRANS CARD --}}
                @php
                    $isMidtransActive = ($activeGateway === 'midtrans');
                    $isMidtransProd = (bool) config('services.midtrans.is_production', false);
                    $hasMidtransKey = !empty(config('services.midtrans.server_key'));
                    $midtransKeyMasked = $hasMidtransKey ? substr(config('services.midtrans.server_key'), 0, 10) . '...' : 'Belum Dikonfigurasi';
                @endphp
                <div wire:click="switchGateway('midtrans')"
                    class="cursor-pointer relative rounded-2xl p-4 sm:p-5 transition-all duration-200 border-2 {{ $isMidtransActive ? 'border-primary-500 bg-primary-50/20 dark:bg-primary-950/20 shadow-md ring-2 ring-primary-500/20' : 'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center font-black text-sm sm:text-base shrink-0 {{ $isMidtransActive ? 'bg-primary-600 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                MT
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-sm sm:text-base text-gray-900 dark:text-white flex flex-wrap items-center gap-1.5 sm:gap-2">
                                    <span>Midtrans</span>
                                    <span class="text-[10px] uppercase font-mono px-2 py-0.5 rounded-full border shrink-0 {{ $isMidtransProd ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800 font-bold' : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800 font-bold' }}">
                                        {{ $isMidtransProd ? 'Production' : 'Sandbox' }}
                                    </span>
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">Core API Snap / QRIS GoPay</p>
                            </div>
                        </div>

                        <div class="shrink-0">
                            @if($isMidtransActive)
                                <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 rounded-full text-xs font-bold bg-primary-100 text-primary-800 dark:bg-primary-950/80 dark:text-primary-300 border border-primary-300 dark:border-primary-800 whitespace-nowrap">
                                    <x-filament::icon icon="heroicon-s-check-circle" class="w-4 h-4 text-primary-600 dark:text-primary-400 shrink-0" />
                                    <span>Aktif</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 whitespace-nowrap">
                                    Pilih Gateway
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 space-y-1.5 text-xs">
                        <div class="flex items-center justify-between gap-2 text-gray-500">
                            <span class="shrink-0">Server Key:</span>
                            <span class="font-mono text-[11px] text-gray-700 dark:text-gray-300 truncate">{{ $midtransKeyMasked }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 text-gray-500">
                            <span class="shrink-0">Biaya Layanan (MDR):</span>
                            <span class="font-semibold text-gray-700 dark:text-gray-300 shrink-0">~0.7% QRIS</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 text-gray-500">
                            <span class="shrink-0">Status Setup:</span>
                            <span class="shrink-0 {{ $hasMidtransKey ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-rose-500 font-semibold' }}">
                                {{ $hasMidtransKey ? 'Siap Digunakan' : 'Kunci Belum Ada' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- BELIBAYAR CARD --}}
                @php
                    $isBelibayarActive = ($activeGateway === 'belibayar');
                    $isBelibayarProd = (bool) config('services.belibayar.is_production', false);
                    $hasBelibayarKey = !empty(config('services.belibayar.api_key'));
                    $belibayarKeyMasked = $hasBelibayarKey ? substr(config('services.belibayar.api_key'), 0, 10) . '...' : 'Belum Dikonfigurasi';
                @endphp
                <div wire:click="switchGateway('belibayar')"
                    class="cursor-pointer relative rounded-2xl p-4 sm:p-5 transition-all duration-200 border-2 {{ $isBelibayarActive ? 'border-blue-500 bg-blue-50/20 dark:bg-blue-950/20 shadow-md ring-2 ring-blue-500/20' : 'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center font-black text-sm sm:text-base shrink-0 {{ $isBelibayarActive ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                BYR
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-sm sm:text-base text-gray-900 dark:text-white flex flex-wrap items-center gap-1.5 sm:gap-2">
                                    <span>Belibayar.id</span>
                                    <span class="text-[10px] uppercase font-mono px-2 py-0.5 rounded-full border shrink-0 {{ $isBelibayarProd ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800 font-bold' : 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800 font-bold' }}">
                                        {{ $isBelibayarProd ? 'Production' : 'Sandbox' }}
                                    </span>
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">Direct API v1 QRIS Dinamis</p>
                            </div>
                        </div>

                        <div class="shrink-0">
                            @if($isBelibayarActive)
                                <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300 border border-blue-300 dark:border-blue-800 whitespace-nowrap">
                                    <x-filament::icon icon="heroicon-s-check-circle" class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0" />
                                    <span>Aktif</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 whitespace-nowrap">
                                    Pilih Gateway
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 space-y-1.5 text-xs">
                        <div class="flex items-center justify-between gap-2 text-gray-500">
                            <span class="shrink-0">API Key:</span>
                            <span class="font-mono text-[11px] text-gray-700 dark:text-gray-300 truncate">{{ $belibayarKeyMasked }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 text-gray-500">
                            <span class="shrink-0">Fitur:</span>
                            <span class="font-semibold text-gray-700 dark:text-gray-300 truncate">Direct QRIS + Auto Callback</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 text-gray-500">
                            <span class="shrink-0">Status Setup:</span>
                            <span class="shrink-0 {{ $hasBelibayarKey ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-rose-500 font-semibold' }}">
                                {{ $hasBelibayarKey ? 'Siap Digunakan' : 'Kunci Belum Ada' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-3.5 bg-slate-50 dark:bg-gray-800/40 rounded-xl border border-slate-200 dark:border-gray-700/60 text-xs text-slate-600 dark:text-slate-300 flex items-start gap-2.5 leading-relaxed">
                <x-filament::icon icon="heroicon-m-information-circle" class="w-4 h-4 text-primary-500 mt-0.5 shrink-0" />
                <span>
                    <strong>Catatan Arsitektur Dual Gateway:</strong> Pengalihan gateway disimpan pada cache sistem secara persisten. Tagihan transaksi yang sudah dibuat sebelumnya tetap akan diverifikasi secara otomatis ke platform awal masing-masing tanpa gangguan.
                </span>
            </div>
        </div>

        {{-- WEBHOOK & INTEGRATION URLS --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 sm:p-6 shadow-sm space-y-4">
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-link" class="w-5 h-5 text-indigo-500 shrink-0" />
                    <span>Konfigurasi Webhook & Callback URL</span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Gunakan URL berikut di Dashboard Merchant penyedia pembayaran untuk menerima notifikasi pelunasan otomatis.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- Belibayar Webhook --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700/60 space-y-2.5" x-data="{ copied: false }">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs text-gray-900 dark:text-white flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                            Belibayar Callback URL
                        </span>
                        <span class="text-[10px] text-gray-400 uppercase font-mono font-semibold px-1.5 py-0.5 rounded bg-gray-200/60 dark:bg-gray-700/60">POST</span>
                    </div>
                    @php $belibayarWebhook = route('belibayar.webhook'); @endphp
                    <div class="flex items-center gap-2 bg-white dark:bg-gray-900 p-2 rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-mono text-gray-800 dark:text-gray-200">
                        <span class="truncate flex-1 min-w-0 select-all text-[11px] sm:text-xs">{{ $belibayarWebhook }}</span>
                        <button type="button"
                            @click="navigator.clipboard.writeText('{{ $belibayarWebhook }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-1 rounded transition-all shrink-0 text-xs"
                            :class="copied ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 font-semibold' : 'text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800'"
                            title="Salin URL">
                            <template x-if="!copied">
                                <span class="flex items-center gap-1">
                                    <x-filament::icon icon="heroicon-o-clipboard-document" class="w-4 h-4" />
                                    <span class="hidden sm:inline text-[11px]">Salin</span>
                                </span>
                            </template>
                            <template x-if="copied">
                                <span class="flex items-center gap-1">
                                    <x-filament::icon icon="heroicon-s-check" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                                    <span class="text-[11px]">Tersalin!</span>
                                </span>
                            </template>
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                        Masukkan URL ini di menu <strong>Pengaturan &gt; Webhook / Callback</strong> di dashboard Belibayar.id.
                    </p>
                </div>

                {{-- Midtrans Webhook --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700/60 space-y-2.5" x-data="{ copied: false }">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs text-gray-900 dark:text-white flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                            Midtrans Notification URL
                        </span>
                        <span class="text-[10px] text-gray-400 uppercase font-mono font-semibold px-1.5 py-0.5 rounded bg-gray-200/60 dark:bg-gray-700/60">POST</span>
                    </div>
                    @php $midtransWebhook = route('midtrans.webhook'); @endphp
                    <div class="flex items-center gap-2 bg-white dark:bg-gray-900 p-2 rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-mono text-gray-800 dark:text-gray-200">
                        <span class="truncate flex-1 min-w-0 select-all text-[11px] sm:text-xs">{{ $midtransWebhook }}</span>
                        <button type="button"
                            @click="navigator.clipboard.writeText('{{ $midtransWebhook }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="inline-flex items-center gap-1 px-2 py-1 rounded transition-all shrink-0 text-xs"
                            :class="copied ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 font-semibold' : 'text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800'"
                            title="Salin URL">
                            <template x-if="!copied">
                                <span class="flex items-center gap-1">
                                    <x-filament::icon icon="heroicon-o-clipboard-document" class="w-4 h-4" />
                                    <span class="hidden sm:inline text-[11px]">Salin</span>
                                </span>
                            </template>
                            <template x-if="copied">
                                <span class="flex items-center gap-1">
                                    <x-filament::icon icon="heroicon-s-check" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                                    <span class="text-[11px]">Tersalin!</span>
                                </span>
                            </template>
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                        Masukkan URL ini di menu <strong>Settings &gt; Configuration &gt; Payment Notification URL</strong> di MAP Midtrans.
                    </p>
                </div>
            </div>
        </div>

        {{-- SYSTEM & ENVIRONMENT DIAGNOSTICS --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 sm:p-6 shadow-sm space-y-4">
            <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-cpu-chip" class="w-5 h-5 text-emerald-500 shrink-0" />
                <span>Informasi Lingkungan (Environment Diagnostics)</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 text-xs">
                <div class="p-3.5 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-200 dark:border-gray-700/60">
                    <span class="text-gray-400 block mb-1">App Environment</span>
                    <span class="font-mono font-bold text-gray-900 dark:text-white uppercase">{{ app()->environment() }}</span>
                </div>
                <div class="p-3.5 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-200 dark:border-gray-700/60">
                    <span class="text-gray-400 block mb-1">Laravel / PHP Version</span>
                    <span class="font-mono font-bold text-gray-900 dark:text-white truncate block">v{{ app()->version() }} / PHP {{ phpversion() }}</span>
                </div>
                <div class="p-3.5 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-200 dark:border-gray-700/60">
                    <span class="text-gray-400 block mb-1">Default .env Gateway</span>
                    <span class="font-mono font-bold text-gray-900 dark:text-white uppercase truncate block">{{ config('services.payment_gateway', 'midtrans') }}</span>
                </div>
                <div class="p-3.5 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-200 dark:border-gray-700/60">
                    <span class="text-gray-400 block mb-1">Simulasi Sandbox</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Aktif & Siap
                    </span>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>