<x-filament-panels::page>
    <style>
        @media (min-width: 850px) {
            .payment-grid-layout {
                display: grid !important;
                grid-template-columns: 1.35fr 1fr !important;
                align-items: start !important;
                gap: 1.5rem !important;
            }
        }
    </style>

    <div x-data="paymentApp({
            checkUrl: '{{ route('submissions.payment.check', $record->id) }}',
            regenerateUrl: '{{ route('submissions.payment.regenerate', $record->id) }}',
            initialStatus: '{{ $payment ? $payment->payment_status : ($record->payment_status === 'paid' ? 'paid' : 'pending') }}',
            initialExpiresAt: '{{ $payment && $payment->expired_at ? $payment->expired_at->toIso8601String() : '' }}',
            isExtracting: {{ $isExtracting ? 'true' : 'false' }},
            initialQrisUrl: '{{ $payment ? $payment->qris_url : '' }}',
            initialOrderId: '{{ $payment ? $payment->order_id : '' }}'
         })" x-init="initPayment()" class="space-y-6">

        <!-- Top Notification Banner for Status -->
        <div
            class="flex items-center justify-between p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-lg">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Pembayaran QRIS Submission
                        {{ $record->id }}
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Selesaikan pembayaran untuk mengaktifkan Letter
                        of Acceptance (LOA) secara otomatis.</p>
                </div>
            </div>
            <div>
                <template x-if="status === 'paid'">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                        <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                        Pembayaran Berhasil
                    </span>
                </template>
                <template x-if="status === 'pending' && !isExpired">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                        Menunggu Pembayaran
                    </span>
                </template>
                <template x-if="status === 'expired' || isExpired">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-300 dark:border-rose-800">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        QRIS Kedaluwarsa
                    </span>
                </template>
            </div>
        </div>

        <!-- Extracting / Loading State -->
        <template x-if="isExtracting">
            <div
                class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-8 text-center shadow-sm">
                <div
                    class="inline-flex p-4 bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 rounded-2xl mb-4 animate-bounce">
                    <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Mengekstrak Metadata & Menghitung
                    Harga...</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-4">
                    Sistem sedang memproses naskah Anda. Halaman akan diperbarui otomatis setelah data diperoleh.
                </p>
            </div>
        </template>

        <!-- Main Layout -->
        <template x-if="!isExtracting">
            <div class="payment-grid-layout flex flex-col gap-6">

                <!-- Left Column: Details -->
                <div class="w-full space-y-6">

                    <!-- Metadata Card -->
                    <div
                        class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                        <h3
                            class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <span>Informasi Naskah</span>
                        </h3>

                        <div class="space-y-4 text-sm">
                            <div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 block mb-0.5">Judul
                                    Artikel:</span>
                                <span class="font-bold text-gray-900 dark:text-white leading-snug block">
                                    {{ !empty($record->title) ? $record->title : 'Sedang diverifikasi...' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-4 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block mb-0.5">Jurnal
                                        Target:</span>
                                    <span
                                        class="font-semibold text-gray-800 dark:text-gray-200">{{ $record->journal?->name ?? '-' }}</span>
                                    <span
                                        class="text-[11px] text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 px-2 py-0.5 rounded inline-block mt-0.5 font-medium">
                                        {{ $record->isExternal() ? 'Internasional' : 'Nasional ISSN' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block mb-0.5">Layanan
                                        DOI:</span>
                                    <span
                                        class="font-semibold {{ $record->want_doi ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }}">
                                        {{ $record->want_doi ? 'Dengan DOI' : 'Tanpa DOI' }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block mb-0.5">Jumlah
                                        Penulis:</span>
                                    <span class="font-bold text-gray-900 dark:text-white">
                                        {{ $pricing ? $pricing['author_count'] : 1 }} Penulis
                                    </span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block mb-0.5">Email
                                        Korespondensi:</span>
                                    <span
                                        class="font-medium text-gray-800 dark:text-gray-200 truncate block">{{ $record->email }}</span>
                                </div>
                            </div>

                            @if(is_array($record->authors) && count($record->authors) > 0)
                                <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1">Daftar Penulis:</span>
                                    <ul
                                        class="text-xs text-gray-700 dark:text-gray-300 space-y-1 bg-gray-50 dark:bg-gray-800/40 p-3 rounded-lg border border-gray-100 dark:border-gray-800">
                                        @foreach($record->authors as $index => $author)
                                            <li class="flex items-center gap-1.5">
                                                <span
                                                    class="w-4 h-4 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 flex items-center justify-center text-[10px] font-bold">{{ $index + 1 }}</span>
                                                <span
                                                    class="font-medium">{{ is_array($author) ? ($author['name'] ?? '-') : $author }}</span>
                                                @if(is_array($author) && !empty($author['institution']))
                                                    <span class="text-gray-400">({{ $author['institution'] }})</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column: QRIS Display & Payment Actions -->
                <div class="w-full">
                    <div
                        class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm sticky top-6 text-center">

                        <!-- When Paid -->
                        <template x-if="status === 'paid'">
                            <div class="py-6 space-y-4">
                                <div
                                    class="w-16 h-16 bg-blue-100 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center mx-auto shadow-inner">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="3"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-black text-gray-900 dark:text-white">Pembayaran Berhasil!</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                                    Pembayaran telah terverifikasi. Pengajuan LOA Anda telah otomatis disetujui oleh
                                    sistem.
                                </p>
                                <div
                                    class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 rounded-xl p-3 text-xs text-blue-800 dark:text-blue-300">
                                    Status LOA: <strong
                                        class="font-bold text-blue-700 dark:text-blue-400 uppercase">Approved
                                        (Disetujui)</strong>
                                </div>
                                <div class="pt-2 space-y-2">
                                    <a href="{{ route('public.invoice.preview', ['record' => $record]) }}"
                                        target="_blank"
                                        class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition-all shadow-md">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                        <span>Download Invoice / Bukti Bayar PDF</span>
                                    </a>
                                    <a href="{{ \App\Filament\Resources\Submissions\SubmissionResource::getUrl('index') }}"
                                        class="w-full inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-300 font-bold py-2 px-4 rounded-xl text-xs transition-all">
                                        <span>Kembali ke Daftar Naskah</span>
                                    </a>
                                </div>
                            </div>
                        </template>

                        <!-- When Pending -->
                        <template x-if="status === 'pending' && !isExpired">
                            <div>
                                <div
                                    class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800 mb-4">
                                    <div
                                        class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 font-semibold">
                                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                                        <span>Scan QRIS</span>
                                    </div>

                                    <!-- Countdown Timer -->
                                    <div
                                        class="text-xs font-mono bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 px-2 py-0.5 rounded flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <span x-text="countdownText">15:00</span>
                                    </div>
                                </div>

                                <!-- Order ID -->
                                <div
                                    class="mb-4 text-left bg-gray-50 dark:bg-gray-800/40 p-3 rounded-lg border border-gray-100 dark:border-gray-800 text-xs">
                                    <div class="flex justify-between text-gray-500 dark:text-gray-400 mb-1">
                                        <span>Order ID:</span>
                                        <span class="font-mono text-gray-900 dark:text-white font-bold"
                                            x-text="orderId"></span>
                                    </div>
                                </div>

                                <!-- QRIS Image Box -->
                                <div
                                    class="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-100 dark:border-gray-800 mb-4 flex flex-col items-center justify-center">
                                    <template x-if="qrisUrl">
                                        <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm">
                                            <img :src="qrisUrl" alt="QRIS Midtrans"
                                                class="w-56 h-56 object-contain rounded-lg">
                                        </div>
                                    </template>
                                    <template x-if="!qrisUrl">
                                        <div
                                            class="w-56 h-56 flex flex-col items-center justify-center text-gray-400 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                                            <svg class="w-8 h-8 animate-spin text-blue-600 mb-2" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                            <span class="text-xs">Membuat QRIS...</span>
                                        </div>
                                    </template>

                                    <div class="mt-3 text-[11px] text-gray-500 dark:text-gray-400 font-medium">
                                        Mendukung GoPay, OVO, DANA, BCA, Mandiri & Seluruh M-Banking
                                    </div>

                                    @if(!config('services.midtrans.is_production', false))
                                        <div
                                            class="mt-3 w-full p-2.5 bg-amber-50/90 dark:bg-amber-950/40 rounded-xl border border-amber-200 dark:border-amber-800 text-[11px] text-amber-900 dark:text-amber-200 text-left space-y-1.5 shadow-sm">
                                            <div
                                                class="font-bold flex items-center gap-1.5 text-amber-800 dark:text-amber-300">
                                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 1-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                                                </svg>
                                                <span>Petunjuk Simulasi Sandbox:</span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                                                <button type="button"
                                                    @click="navigator.clipboard.writeText(qrisUrl); alert('URL QRIS disalin! Paste ke kolom simulator Midtrans.')"
                                                    class="px-2.5 py-1 bg-white dark:bg-gray-800 hover:bg-amber-100 text-amber-800 dark:text-amber-200 font-semibold rounded-md border border-amber-300 dark:border-amber-700 text-[10px] transition-colors">
                                                    Salin URL
                                                </button>
                                                <a href="https://simulator.sandbox.midtrans.com/v2/qris/index"
                                                    target="_blank"
                                                    class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-md text-[10px] transition-colors inline-flex items-center gap-1 ml-auto">
                                                    <span>Buka Simulator</span>
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Integrated Pricing Breakdown under QRIS -->
                                <div
                                    class="bg-gray-50 dark:bg-gray-800/60 p-4 rounded-xl border border-gray-200 dark:border-gray-700/60 text-left mb-4">
                                    <div class="space-y-2 text-xs">
                                        <div class="flex justify-between items-center text-gray-500 dark:text-gray-400">
                                            <span>Paket:</span>
                                            <span
                                                class="font-semibold text-gray-800 dark:text-gray-200">{{ $pricing['tier_name'] ?? 'Standar' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center text-gray-500 dark:text-gray-400">
                                            <span>Biaya Publikasi:</span>
                                            <span class="font-semibold text-gray-800 dark:text-gray-200">Rp
                                                {{ number_format($pricing['gross_amount'] ?? 0, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <div
                                        class="pt-2.5 mt-2 border-t border-gray-200 dark:border-gray-700 flex justify-between items-baseline">
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Total
                                            Tagihan:</span>
                                        <span class="text-xl font-black text-blue-600 dark:text-blue-400">
                                            Rp {{ number_format($pricing['gross_amount'] ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="space-y-2">
                                    <button @click="checkStatus()" :disabled="isChecking"
                                        class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-bold py-2.5 px-4 rounded-xl text-xs sm:text-sm transition-all shadow-sm active:scale-[0.99]">
                                        <svg x-show="isChecking" class="w-4 h-4 animate-spin" fill="none"
                                            viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        <svg x-show="!isChecking" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                        </svg>
                                        <span x-text="isChecking ? 'Memeriksa...' : 'Periksa Status Pembayaran'"></span>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- When Expired -->
                        <template x-if="status === 'expired' || isExpired">
                            <div class="py-6 space-y-4">
                                <div
                                    class="w-16 h-16 bg-rose-100 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 rounded-full flex items-center justify-center mx-auto shadow-inner">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-black text-gray-900 dark:text-white">QRIS Telah Kedaluwarsa
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                                    Waktu pembayaran telah habis. Klik tombol di bawah untuk membuat QRIS baru.
                                </p>
                                <div class="pt-2">
                                    <button @click="regenerateQris()" :disabled="isRegenerating"
                                        class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition-all shadow-md">
                                        <svg x-show="isRegenerating" class="w-4 h-4 animate-spin" fill="none"
                                            viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        <span x-text="isRegenerating ? 'Membuat QRIS...' : 'Buat QRIS Baru'"></span>
                                    </button>
                                </div>
                            </div>
                        </template>

                    </div>
                </div>

            </div>
        </template>
    </div>

    <script>
        function paymentApp(config) {
            return {
                checkUrl: config.checkUrl,
                regenerateUrl: config.regenerateUrl,
                status: config.initialStatus,
                expiresAt: config.initialExpiresAt ? new Date(config.initialExpiresAt) : null,
                isExtracting: config.isExtracting,
                qrisUrl: config.initialQrisUrl,
                orderId: config.initialOrderId,
                isExpired: false,
                isChecking: false,
                isRegenerating: false,
                countdownText: '15:00',
                pollTimer: null,
                countdownTimer: null,

                initPayment() {
                    this.updateCountdown();
                    this.countdownTimer = setInterval(() => this.updateCountdown(), 1000);

                    this.pollTimer = setInterval(() => {
                        if (this.status === 'pending' && !this.isExpired) {
                            this.checkStatus(true);
                        } else if (this.isExtracting) {
                            this.checkStatus(true);
                        }
                    }, 5000);
                },

                updateCountdown() {
                    if (!this.expiresAt || this.status !== 'pending') {
                        return;
                    }

                    const now = new Date();
                    const diff = this.expiresAt.getTime() - now.getTime();

                    if (diff <= 0) {
                        this.isExpired = true;
                        this.countdownText = '00:00';
                        return;
                    }

                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    this.countdownText = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                },

                async checkStatus(silent = false) {
                    if (!silent) this.isChecking = true;

                    try {
                        const res = await fetch(this.checkUrl, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const data = await res.json();

                        if (data.is_paid || data.status === 'paid') {
                            this.status = 'paid';
                            this.isExtracting = false;
                        } else if (data.status === 'expired' || data.is_expired) {
                            this.isExpired = true;
                            this.status = 'expired';
                        } else if (data.status === 'extracting') {
                            this.isExtracting = true;
                        } else {
                            if (this.isExtracting) {
                                window.location.reload();
                            }
                        }
                    } catch (e) {
                        console.error('Check status error:', e);
                    } finally {
                        if (!silent) this.isChecking = false;
                    }
                },

                async regenerateQris() {
                    this.isRegenerating = true;
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        const res = await fetch(this.regenerateUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const data = await res.json();
                        if (data.success) {
                            this.status = 'pending';
                            this.isExpired = false;
                            this.orderId = data.order_id;
                            this.qrisUrl = data.qris_url;
                            this.expiresAt = data.expired_at ? new Date(data.expired_at) : new Date(Date.now() + 15 * 60000);
                            this.updateCountdown();
                        } else {
                            alert(data.message || 'Gagal membuat QRIS baru.');
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan jaringan.');
                    } finally {
                        this.isRegenerating = false;
                    }
                }
            };
        }
    </script>
</x-filament-panels::page>