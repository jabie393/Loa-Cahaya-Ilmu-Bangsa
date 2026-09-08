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
            checkUrl: '{{ route('submissions.payment.replace-pdf.check', $record->id) }}',
            regenerateUrl: '{{ route('submissions.payment.replace-pdf.regenerate', $record->id) }}',
            initialStatus: '{{ $payment && $payment->payment_status === 'paid' ? 'paid' : ($payment ? $payment->payment_status : 'pending') }}',
            initialExpiresAt: '{{ $payment && $payment->expired_at ? $payment->expired_at->toIso8601String() : '' }}',
            isExtracting: false,
            initialQrisUrl: '{{ $payment ? $payment->qris_url : '' }}',
            initialOrderId: '{{ $payment ? $payment->order_id : '' }}',
            errorMessage: '{{ $errorMessage ? addslashes($errorMessage) : '' }}'
         })" x-init="initPayment()" class="space-y-6">

        <!-- Top Notification Banner for Status -->
        <div
            class="flex items-center justify-between p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-lg">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Pembayaran Layanan Ganti PDF Naskah #{{ $record->id }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pembaruan file artikel ilmiah dan sinkronisasi otomatis ke OJS.</p>
                </div>
            </div>
            <div>
                <template x-if="status === 'paid'">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                        PDF Telah Diperbarui
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

        <!-- Main Layout -->
        <div class="payment-grid-layout flex flex-col gap-6">

            <!-- Left Column: Details -->
            <div class="w-full space-y-6">

                <!-- Metadata Card -->
                <div
                    class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                    <h3
                        class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <span>Informasi Naskah</span>
                    </h3>

                    <div class="space-y-4 text-sm">
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 block mb-0.5">Judul Artikel:</span>
                            <span class="font-bold text-gray-900 dark:text-white leading-snug block">
                                {{ !empty($record->title) ? $record->title : '-' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-3 border-t border-gray-100 dark:border-gray-800">
                            <div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 block mb-0.5">Jurnal:</span>
                                <span
                                    class="font-semibold text-gray-800 dark:text-gray-200">{{ $record->journal?->name ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 block mb-0.5">Penulis Utama:</span>
                                <span
                                    class="font-semibold text-gray-800 dark:text-gray-200">{{ $record->author_name ?? '-' }}</span>
                            </div>
                        </div>

                        <div
                            class="pt-3 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-600 dark:text-gray-400 leading-relaxed bg-amber-50/50 dark:bg-amber-950/20 p-3 rounded-lg border border-amber-200 dark:border-amber-800/40">
                            <strong>Informasi Layanan:</strong> Pembayaran ini digunakan untuk biaya penggantian file naskah PDF dan pembaruan otomatis ke server OJS. Jumlah penulis pada file baru telah diverifikasi sesuai dengan data awal.
                        </div>
                    </div>
                </div>

                <!-- Price Breakdown Card -->
                <div
                    class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                    <h3
                        class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v8.25m0-8.25a60.074 60.074 0 0 1 15.797-2.101c.727-.198 1.453.342 1.453 1.096V4.5m0 0v10.5m0-10.5a60.07 60.07 0 0 0-15.797 2.101c-.727.198-1.453-.342-1.453-1.096V6m18 8.25a60.07 60.07 0 0 1-15.797 2.101c-.727.198-1.453-.342-1.453-1.096V14.25" />
                        </svg>
                        <span>Rincian Pembayaran</span>
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center pb-2 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-gray-600 dark:text-gray-400">Layanan Ganti PDF Naskah</span>
                            <span class="font-semibold text-gray-900 dark:text-white">Rp 25.000</span>
                        </div>
                        <div class="flex justify-between items-center pt-1 font-bold text-base text-gray-900 dark:text-white">
                            <span>Total Tagihan:</span>
                            <span class="text-primary-600 dark:text-primary-400 font-extrabold text-lg">Rp 25.000</span>
                        </div>
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
                                class="w-16 h-16 bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mx-auto shadow-inner">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="3"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white">PDF Berhasil Diperbarui!</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                                Pembayaran terverifikasi. File naskah PDF telah resmi diperbarui pada sistem dan disinkronkan ke server OJS.
                            </p>
                            <div class="pt-2 space-y-2">
                                <a href="{{ \App\Filament\Resources\Submissions\SubmissionResource::getUrl('view', ['record' => $record]) }}"
                                    class="w-full inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition-all shadow-md">
                                    <span>Lihat Naskah (Review Page)</span>
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
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
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
                                <template x-if="qrisUrl && !errorMessage">
                                    <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm">
                                        <img :src="qrisUrl" alt="QRIS Midtrans"
                                            class="w-56 h-56 object-contain rounded-lg">
                                    </div>
                                </template>
                                <template x-if="errorMessage">
                                    <div class="w-full p-4 bg-rose-50 dark:bg-rose-950/40 rounded-xl border border-rose-200 dark:border-rose-800 text-center space-y-2.5">
                                        <div class="inline-flex p-2 bg-rose-100 dark:bg-rose-900/50 rounded-full text-rose-600 dark:text-rose-400">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                            </svg>
                                        </div>
                                        <h4 class="text-xs font-bold text-rose-800 dark:text-rose-200 leading-snug" x-text="errorMessage"></h4>
                                        <div class="pt-1">
                                            <button type="button" @click="regenerateQris()" :disabled="isRegenerating"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs shadow-sm transition-colors">
                                                <svg x-show="isRegenerating" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span x-text="isRegenerating ? 'Menghubungkan...' : 'Coba Hubungkan Ulang'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                Buka aplikasi e-wallet / mobile banking Anda (GoPay, BCA, Mandiri, OVO, Dana, dll) lalu scan QR code di atas.
                            </p>
                        </div>
                    </template>

                    <!-- When Expired -->
                    <template x-if="status === 'expired' || isExpired">
                        <div class="py-6 space-y-3">
                            <div
                                class="w-14 h-14 bg-rose-100 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 rounded-full flex items-center justify-center mx-auto">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">QRIS Telah Kedaluwarsa</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                                Batas waktu pembayaran telah habis. Silakan buat kode QRIS baru untuk melanjutkan.
                            </p>
                            <div class="pt-2">
                                <button type="button" @click="regenerateQris()" :disabled="isRegenerating"
                                    class="w-full inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition-all shadow-md">
                                    <svg x-show="isRegenerating" class="w-4 h-4 animate-spin" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span x-text="isRegenerating ? 'Membuat QRIS Baru...' : 'Buat QRIS Baru'"></span>
                                </button>
                            </div>
                        </div>
                    </template>

                </div>
            </div>

        </div>

    </div>

    <script>
        function paymentApp(config) {
            return {
                checkUrl: config.checkUrl,
                regenerateUrl: config.regenerateUrl,
                status: config.initialStatus,
                expiresAt: config.initialExpiresAt,
                qrisUrl: config.initialQrisUrl,
                orderId: config.initialOrderId,
                errorMessage: config.errorMessage,
                isExpired: false,
                isRegenerating: false,
                countdownText: '15:00',
                timerInterval: null,
                pollingInterval: null,

                initPayment() {
                    if (this.status === 'paid') {
                        return;
                    }
                    this.startCountdown();
                    this.startPolling();
                },

                startCountdown() {
                    if (!this.expiresAt) return;

                    const updateTimer = () => {
                        const now = new Date().getTime();
                        const expireTime = new Date(this.expiresAt).getTime();
                        const distance = expireTime - now;

                        if (distance <= 0) {
                            this.isExpired = true;
                            this.status = 'expired';
                            this.countdownText = '00:00';
                            clearInterval(this.timerInterval);
                            clearInterval(this.pollingInterval);
                            return;
                        }

                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                        this.countdownText = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                    };

                    updateTimer();
                    this.timerInterval = setInterval(updateTimer, 1000);
                },

                startPolling() {
                    this.pollingInterval = setInterval(async () => {
                        if (this.status === 'paid' || this.isExpired) {
                            clearInterval(this.pollingInterval);
                            return;
                        }

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
                                clearInterval(this.pollingInterval);
                                clearInterval(this.timerInterval);
                            } else if (data.is_expired || data.status === 'expired') {
                                this.isExpired = true;
                                this.status = 'expired';
                                clearInterval(this.pollingInterval);
                                clearInterval(this.timerInterval);
                            }
                        } catch (e) {
                            console.error('Polling error:', e);
                        }
                    }, 3000);
                },

                async regenerateQris() {
                    this.isRegenerating = true;
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const res = await fetch(this.regenerateUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token || '{{ csrf_token() }}'
                            }
                        });
                        const data = await res.json();

                        if (data.success) {
                            this.qrisUrl = data.qris_url;
                            this.orderId = data.order_id;
                            this.expiresAt = data.expired_at;
                            this.errorMessage = '';
                            this.status = 'pending';
                            this.isExpired = false;

                            clearInterval(this.timerInterval);
                            clearInterval(this.pollingInterval);

                            this.startCountdown();
                            this.startPolling();
                        } else {
                            alert(data.message || 'Gagal membuat QRIS.');
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan saat membuat QRIS baru.');
                    } finally {
                        this.isRegenerating = false;
                    }
                }
            };
        }
    </script>
</x-filament-panels::page>
