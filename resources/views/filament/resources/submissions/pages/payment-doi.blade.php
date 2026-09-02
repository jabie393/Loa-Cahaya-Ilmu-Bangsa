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
            checkUrl: '{{ route('submissions.payment.doi.check', $record->id) }}',
            regenerateUrl: '{{ route('submissions.payment.doi.regenerate', $record->id) }}',
            initialStatus: '{{ $record->has_doi && !empty($record->repository_identifier) ? 'paid' : ($payment ? $payment->payment_status : 'pending') }}',
            initialExpiresAt: '{{ $payment && $payment->expired_at ? $payment->expired_at->toIso8601String() : '' }}',
            isExtracting: false,
            initialQrisUrl: '{{ $payment ? $payment->qris_url : '' }}',
            initialOrderId: '{{ $payment ? $payment->order_id : '' }}',
            initialDoi: '{{ $record->repository_identifier ?? '' }}',
            initialDoiUrl: '{{ $record->repository_redirect_url ?? '' }}'
         })" x-init="initPayment()" class="space-y-6">

        <!-- Top Notification Banner for Status -->
        <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-lg">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Pembayaran Add-on DOI Submission {{ $record->id }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pemberian pengenal unik Repository Identifier (DOI) resmi untuk artikel ilmiah Anda.</p>
                </div>
            </div>
            <div>
                <template x-if="status === 'paid'">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                        <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                        DOI Telah Aktif
                    </span>
                </template>
                <template x-if="status === 'pending' && !isExpired">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                        Menunggu Pembayaran
                    </span>
                </template>
                <template x-if="status === 'expired' || isExpired">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-300 dark:border-rose-800">
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
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <span>Informasi Artikel Terbitan</span>
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
                                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $record->journal?->name ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 block mb-0.5">Status DOI Saat Ini:</span>
                                <template x-if="status === 'paid' && doiIdentifier">
                                    <div class="flex items-center gap-2">
                                        <a :href="doiUrl || ('http://127.0.0.1:8001/' + doiIdentifier)" target="_blank"
                                           class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 font-bold text-xs rounded-lg border border-blue-300 dark:border-blue-700 hover:underline">
                                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                            <span x-text="doiIdentifier"></span>
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                            </svg>
                                        </a>
                                    </div>
                                </template>
                                <template x-if="status !== 'paid' || !doiIdentifier">
                                    <span class="font-bold text-amber-600 dark:text-amber-400">
                                        Belum Ada DOI
                                    </span>
                                </template>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-600 dark:text-gray-400 leading-relaxed bg-blue-50/50 dark:bg-blue-950/20 p-3 rounded-lg border border-emerald-100 dark:border-blue-800/40">
                            <strong>Manfaat Tambah DOI:</strong> Artikel Anda akan mendapatkan link permanen resmi Repository Identifier, memudahkan sitasi di Google Scholar, dan otomatis disinkronkan ke OJS & database repository.
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: QRIS Display & Payment Actions -->
            <div class="w-full">
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm sticky top-6 text-center">

                    <!-- When Paid -->
                    <template x-if="status === 'paid'">
                        <div class="py-6 space-y-4">
                            <div class="w-16 h-16 bg-blue-100 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center mx-auto shadow-inner">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white">DOI Berhasil Diaktifkan!</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                                Pembayaran terverifikasi. Pengenal DOI Repository Identifier telah dibuat dan disinkronkan.
                            </p>
                            <template x-if="doiIdentifier">
                                <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 rounded-xl p-3 text-xs text-blue-800 dark:text-blue-300 space-y-1">
                                    <span class="block text-[11px] uppercase tracking-wider text-blue-600 dark:text-blue-400 font-semibold">Repository Identifier (DOI):</span>
                                    <a :href="doiUrl || ('http://127.0.0.1:8001/' + doiIdentifier)" target="_blank"
                                       class="font-mono font-bold text-sm text-blue-700 dark:text-blue-300 hover:underline block"
                                       x-text="doiIdentifier">
                                    </a>
                                </div>
                            </template>
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
                            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800 mb-4">
                                <div class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                                    <span>Scan QRIS</span>
                                </div>

                                <!-- Countdown Timer -->
                                <div class="text-xs font-mono bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 px-2 py-0.5 rounded flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <span x-text="countdownText">15:00</span>
                                </div>
                            </div>

                            <!-- Order ID -->
                            <div class="mb-4 text-left bg-gray-50 dark:bg-gray-800/40 p-3 rounded-lg border border-gray-100 dark:border-gray-800 text-xs">
                                <div class="flex justify-between text-gray-500 dark:text-gray-400 mb-1">
                                    <span>Order ID:</span>
                                    <span class="font-mono text-gray-900 dark:text-white font-bold" x-text="orderId"></span>
                                </div>
                            </div>

                            <!-- QRIS Image Box -->
                            <div class="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-100 dark:border-gray-800 mb-4 flex flex-col items-center justify-center">
                                <template x-if="qrisUrl">
                                    <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm">
                                        <img :src="qrisUrl" alt="QRIS Midtrans" class="w-56 h-56 object-contain rounded-lg">
                                    </div>
                                </template>
                                <template x-if="!qrisUrl">
                                    <div class="w-56 h-56 flex flex-col items-center justify-center text-gray-400 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                                        <svg class="w-8 h-8 animate-spin text-blue-600 mb-2" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-xs">Membuat QRIS...</span>
                                    </div>
                                </template>

                                <div class="mt-3 text-[11px] text-gray-500 dark:text-gray-400 font-medium">
                                    Mendukung GoPay, OVO, DANA, BCA, Mandiri & Seluruh M-Banking
                                </div>
                            </div>

                            <!-- Pricing Breakdown under QRIS -->
                            <div class="bg-gray-50 dark:bg-gray-800/60 p-4 rounded-xl border border-gray-200 dark:border-gray-700/60 text-left mb-4">
                                <div class="space-y-2 text-xs">
                                    <div class="flex justify-between items-center text-gray-500 dark:text-gray-400">
                                        <span>Layanan:</span>
                                        <span class="font-semibold text-gray-800 dark:text-gray-200">Add-on DOI Repository</span>
                                    </div>
                                    <div class="flex justify-between items-center text-gray-500 dark:text-gray-400">
                                        <span>Biaya:</span>
                                        <span class="font-semibold text-gray-800 dark:text-gray-200">Rp 20.000</span>
                                    </div>
                                </div>
                                <div class="pt-2.5 mt-2 border-t border-gray-200 dark:border-gray-700 flex justify-between items-baseline">
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Total Tagihan:</span>
                                    <span class="text-xl font-black text-blue-600 dark:text-blue-400">
                                        Rp 20.000
                                    </span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="space-y-2">
                                <button @click="checkStatus()" :disabled="isChecking"
                                    class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition-all shadow-sm">
                                    <svg x-show="isChecking" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="isChecking ? 'Memeriksa...' : 'Periksa Status Pembayaran'"></span>
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- When Expired -->
                    <template x-if="status === 'expired' || isExpired">
                        <div class="py-6 space-y-4">
                            <div class="w-16 h-16 bg-rose-100 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 rounded-full flex items-center justify-center mx-auto shadow-inner">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-black text-gray-900 dark:text-white">QRIS Telah Kedaluwarsa</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                                Waktu pembayaran telah habis. Klik tombol di bawah untuk membuat QRIS baru.
                            </p>
                            <div class="pt-2">
                                <button @click="regenerateQris()" :disabled="isRegenerating"
                                    class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition-all shadow-md">
                                    <svg x-show="isRegenerating" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="isRegenerating ? 'Membuat QRIS...' : 'Buat QRIS Baru'"></span>
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
                expiresAt: config.initialExpiresAt ? new Date(config.initialExpiresAt) : null,
                isExtracting: config.isExtracting,
                qrisUrl: config.initialQrisUrl,
                orderId: config.initialOrderId,
                doiIdentifier: config.initialDoi || '',
                doiUrl: config.initialDoiUrl || '',
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
                            if (data.doi_identifier) this.doiIdentifier = data.doi_identifier;
                            if (data.doi_url) this.doiUrl = data.doi_url;
                        } else if (data.status === 'expired' || data.is_expired) {
                            this.isExpired = true;
                            this.status = 'expired';
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
