<x-filament-panels::page>
    @php
        $isPaid = $payment ? $payment->isPaid() : false;
        $isExpired = $payment ? $payment->isExpired() : false;
        $qrisUrl = $payment ? $payment->qris_url : '';
        $orderId = $payment ? $payment->order_id : '';
        $paymentId = $payment ? $payment->id : 0;
        $expiresAtStr = ($payment && $payment->expired_at) ? $payment->expired_at->toIso8601String() : '';
    @endphp

    <style>
        .payment-grid-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            align-items: start;
        }

        @media (min-width: 1024px) {
            .payment-grid-layout {
                grid-template-columns: 7fr 5fr;
            }
        }
    </style>

    <div x-data="bulkPaymentApp({
            checkUrl: '{{ route('payments.check.bulk', ['paymentId' => $paymentId]) }}',
            regenerateUrl: '{{ route('payments.regenerate.bulk', ['paymentId' => $paymentId]) }}',
            initialStatus: '{{ $isPaid ? 'paid' : ($isExpired ? 'expired' : 'pending') }}',
            initialExpiresAt: '{{ $expiresAtStr }}',
            initialQrisUrl: '{{ $qrisUrl }}',
            initialOrderId: '{{ $orderId }}'
        })" x-init="initPayment()" class="space-y-6">

        <!-- Top Notification Banner for Status -->
        <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-lg">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">
                        Pembayaran Kolektif {{ count($submissions) }} Naskah Sekaligus
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Selesaikan pembayaran 1 QRIS untuk menyetujui (Approve) seluruh naskah terpilih secara otomatis.
                    </p>
                </div>
            </div>
            <div>
                <template x-if="status === 'paid'">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                        <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                        Semua Naskah Lunas (Approved)
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

        <!-- Main Layout (2 Columns) -->
        <div class="payment-grid-layout">

            <!-- Left Column: Multiple Submission Cards -->
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-1 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <span>Daftar Naskah yang Dibayar ({{ count($submissions) }} Naskah)</span>
                    </h3>
                </div>

                @foreach($itemsPricing as $index => $item)
                    @php
                        $sub = $item['submission'];
                        $pr = $item['pricing'];
                        $authors = $sub->authors;
                        $authorNames = [];
                        if (is_array($authors)) {
                            foreach ($authors as $a) {
                                $authorNames[] = is_array($a) ? ($a['name'] ?? '') : $a;
                            }
                        }
                    @endphp
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm space-y-3 relative overflow-hidden transition-all hover:border-blue-300 dark:hover:border-blue-700">
                        <div class="absolute top-0 right-0 bg-slate-100 dark:bg-gray-800 px-3 py-1 rounded-bl-xl text-[10.5px] font-mono font-bold text-slate-600 dark:text-slate-400">
                            {{ $sub->id }}
                        </div>

                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Naskah {{ $index + 1 }}</span>
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white leading-snug">
                                {{ $sub->title ?: 'Judul Naskah ' . $sub->id }}
                            </h4>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-100 dark:border-gray-800 text-xs">
                            <div>
                                <span class="text-[10px] text-gray-400 font-semibold block uppercase">Jurnal Target:</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200 block">{{ $sub->journal?->name ?? 'Jurnal CIB' }}</span>
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-slate-400">
                                    {{ $sub->isExternal() ? 'Internasional' : 'Nasional ISSN' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-400 font-semibold block uppercase">Layanan DOI:</span>
                                @if($sub->want_doi)
                                    <span class="font-bold text-blue-600 dark:text-blue-400 block">+ Dengan DOI</span>
                                @else
                                    <span class="font-medium text-gray-500 dark:text-gray-400 block">Tanpa DOI</span>
                                @endif
                                <span class="text-[10.5px] text-gray-500 dark:text-gray-400 block mt-0.5">
                                    {{ count($authorNames) ?: 1 }} Penulis
                                </span>
                            </div>
                        </div>

                        <div class="pt-2.5 border-t border-dashed border-gray-200 dark:border-gray-800 flex justify-between items-center text-xs">
                            <span class="text-slate-500 font-medium">Paket: {{ $pr['tier_name'] }}</span>
                            <span class="font-mono font-bold text-blue-600 dark:text-blue-400 text-sm">
                                Rp {{ number_format($pr['gross_amount'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Right Column: Single QRIS & Total Breakdown -->
            <div class="w-full space-y-6 lg:sticky lg:top-6">
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                    
                    <!-- When Paid -->
                    <template x-if="status === 'paid'">
                        <div class="text-center py-6 space-y-4">
                            <div class="w-16 h-16 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center mx-auto mb-2">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white">Pembayaran Kolektif Berhasil!</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                                Seluruh {{ count($submissions) }} naskah terpilih telah disetujui (Approved) secara otomatis dan disinkronkan ke OJS.
                            </p>
                            <div class="pt-2">
                                <a href="{{ \App\Filament\Resources\Submissions\SubmissionResource::getUrl('index') }}"
                                    class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition-all shadow-md">
                                    <span>Kembali ke Daftar Naskah</span>
                                </a>
                            </div>
                        </div>
                    </template>

                    <!-- When Pending -->
                    <template x-if="status === 'pending' && !isExpired">
                        <div>
                            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800 mb-4">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Scan QRIS Kolektif</span>
                                <div class="flex items-center gap-1.5 text-xs text-amber-600 dark:text-amber-400 font-mono font-bold bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-md">
                                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <span x-text="countdownText">15:00</span>
                                </div>
                            </div>

                            <!-- Order ID -->
                            <div class="mb-3 text-center">
                                <span class="text-[10px] text-gray-400 block uppercase tracking-wider font-semibold">Order ID Transaksi</span>
                                <span class="font-mono text-xs font-bold text-gray-800 dark:text-gray-200" x-text="orderId"></span>
                            </div>

                            <!-- QR Code Box -->
                            <div class="bg-gray-50 dark:bg-gray-950 p-4 rounded-xl border border-gray-200 dark:border-gray-800 flex flex-col items-center justify-center min-h-[220px]">
                                <template x-if="qrisUrl">
                                    <img :src="qrisUrl" alt="QRIS Midtrans Kolektif" class="w-52 h-52 object-contain rounded-lg shadow-sm border border-white">
                                </template>
                                <template x-if="!qrisUrl">
                                    <div class="text-center text-xs text-gray-400 py-8">
                                        <span>Memuat QRIS Midtrans...</span>
                                    </div>
                                </template>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-3 text-center">
                                    Buka GoPay, BCA, Mandiri, ShopeePay, Dana, atau Mobile Banking apa saja untuk scan QR di atas.
                                </p>
                            </div>

                            <!-- Total & Breakdown Underneath QRIS -->
                            <div class="mt-4 p-4 rounded-xl bg-slate-50 dark:bg-gray-950/60 border border-slate-200 dark:border-gray-800 space-y-2 text-xs">
                                <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Rincian Total Kolektif</span>
                                
                                @foreach($itemsPricing as $item)
                                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                        <span class="truncate max-w-[180px]">Naskah {{ $item['submission']->id }}:</span>
                                        <span class="font-mono font-semibold text-gray-800 dark:text-gray-200">
                                            Rp {{ number_format($item['pricing']['gross_amount'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach

                                <div class="pt-2 border-t border-slate-200 dark:border-gray-800 flex justify-between items-baseline">
                                    <span class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider">Total Tagihan:</span>
                                    <span class="text-xl font-black text-blue-600 dark:text-blue-400 font-mono">
                                        Rp {{ number_format($pricing['gross_amount'] ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-4 space-y-2">
                                <button type="button" @click="checkStatus(false)" :disabled="isChecking"
                                    class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition-all shadow-md">
                                    <svg class="w-4 h-4" :class="isChecking ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    <span x-text="isChecking ? 'Memeriksa...' : 'Cek Status Pembayaran'"></span>
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- When Expired -->
                    <template x-if="status === 'expired' || isExpired">
                        <div class="text-center py-6 space-y-4">
                            <div class="w-16 h-16 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-full flex items-center justify-center mx-auto mb-2">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-black text-gray-900 dark:text-white">QRIS Telah Kedaluwarsa</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                                Batas waktu pembayaran telah habis. Klik tombol di bawah untuk membuat QRIS kolektif baru.
                            </p>
                            <div class="pt-2 space-y-2">
                                <button type="button" @click="regenerateQris()" :disabled="isRegenerating"
                                    class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition-all shadow-md">
                                    <svg x-show="isRegenerating" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="isRegenerating ? 'Membuat QRIS Baru...' : 'Buat QRIS Baru'"></span>
                                </button>
                                <a href="{{ \App\Filament\Resources\Submissions\SubmissionResource::getUrl('index') }}"
                                    class="w-full inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 text-slate-700 dark:text-slate-300 font-bold py-2 px-4 rounded-xl text-xs transition-all">
                                    <span>Kembali ke Daftar Naskah</span>
                                </a>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        function bulkPaymentApp(config) {
            return {
                checkUrl: config.checkUrl,
                regenerateUrl: config.regenerateUrl,
                status: config.initialStatus,
                expiresAt: config.initialExpiresAt ? new Date(config.initialExpiresAt) : null,
                qrisUrl: config.initialQrisUrl,
                orderId: config.initialOrderId,
                isExpired: config.initialStatus === 'expired',
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
                    }, 4000);
                },

                updateCountdown() {
                    if (!this.expiresAt) return;
                    const now = new Date().getTime();
                    const distance = this.expiresAt.getTime() - now;

                    if (distance <= 0) {
                        this.countdownText = '00:00';
                        this.isExpired = true;
                        this.status = 'expired';
                        return;
                    }

                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    this.countdownText = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                },

                async checkStatus(silent = false) {
                    if (!silent) this.isChecking = true;
                    try {
                        const res = await fetch(this.checkUrl);
                        const data = await res.json();
                        this.status = data.status;

                        if (data.is_paid || data.status === 'paid') {
                            this.status = 'paid';
                            this.isExpired = false;
                            if (this.pollTimer) clearInterval(this.pollTimer);
                            if (this.countdownTimer) clearInterval(this.countdownTimer);
                        }

                        if (data.is_expired || data.status === 'expired') {
                            this.isExpired = true;
                            this.status = 'expired';
                            if (this.pollTimer) clearInterval(this.pollTimer);
                            if (this.countdownTimer) clearInterval(this.countdownTimer);
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
                        const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.content;
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
                            this.checkUrl = data.check_url;
                            this.regenerateUrl = data.regenerate_url;
                            this.expiresAt = data.expired_at ? new Date(data.expired_at) : new Date(Date.now() + 15 * 60000);
                            
                            this.updateCountdown();
                        } else {
                            alert(data.message || 'Gagal membuat QRIS Baru.');
                        }
                    } catch (e) {
                        console.error('Regenerate error:', e);
                        alert('Terjadi kesalahan koneksi saat membuat QRIS baru.');
                    } finally {
                        this.isRegenerating = false;
                    }
                }
            };
        }
    </script>
</x-filament-panels::page>
