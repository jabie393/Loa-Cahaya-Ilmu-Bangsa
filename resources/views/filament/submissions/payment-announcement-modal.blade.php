<div x-data="{
    isOpen: false,
    dontShowAgain: false,
    init() {
        const seen = localStorage.getItem('cib_payment_announcement_seen_v1') === 'true';
        this.dontShowAgain = false;
        if (!seen) {
            setTimeout(() => {
                this.isOpen = true;
            }, 350);
        }
    },
    closeModal() {
        if (this.dontShowAgain) {
            localStorage.setItem('cib_payment_announcement_seen_v1', 'true');
        } else {
            localStorage.removeItem('cib_payment_announcement_seen_v1');
        }
        this.isOpen = false;
    }
}" 
@open-payment-announcement.window="
    isOpen = true;
    dontShowAgain = false;
"
x-show="isOpen" 
x-cloak
class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto px-4 py-4 sm:px-6 sm:py-6" 
role="dialog"
aria-modal="true" 
aria-labelledby="modal-title">

    <!-- Backdrop with blur -->
    <div x-show="isOpen" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 backdrop-blur-none" 
        x-transition:enter-end="opacity-100 backdrop-blur-md"
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100 backdrop-blur-md"
        x-transition:leave-end="opacity-0 backdrop-blur-none"
        class="fixed inset-0 bg-slate-950/70 transition-opacity" 
        @click="closeModal()"></div>

    <!-- Modal Dialog Card -->
    <div x-show="isOpen" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-6 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-6 scale-95"
        class="relative w-full max-w-3xl max-h-[92vh] flex flex-col transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-2xl transition-all border border-slate-200 dark:border-slate-800">
        
        <!-- Ambient Decorative Top Header Gradient -->
        <div class="h-2 w-full bg-gradient-to-r from-blue-600 via-indigo-600 to-emerald-500"></div>

        <!-- Header Section -->
        <div class="relative px-6 pt-5 pb-4 border-b border-slate-100 dark:border-slate-800 flex items-start justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/20">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </div>
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wide uppercase bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800 mb-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                        Pembaruan Sistem Pembayaran
                    </div>
                    <h3 id="modal-title" class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Integrasi Pembayaran Otomatis QRIS
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Kini seluruh transaksi LOA dan layanan naskah terverifikasi instan tanpa upload bukti manual.
                    </p>
                </div>
            </div>

            <!-- Close button -->
            <button type="button" 
                @click="closeModal()" 
                class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300 transition-colors"
                aria-label="Tutup">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Body Content: Scrollable -->
        <div class="px-6 py-5 overflow-y-auto space-y-4 max-h-[60vh] text-slate-700 dark:text-slate-300 text-xs sm:text-sm">
            
            <!-- Highlight Box: Support Seluruh Bank & E-Wallet -->
            <div class="p-4 rounded-xl bg-gradient-to-br from-slate-50 to-blue-50/40 dark:from-slate-800/40 dark:to-blue-950/20 border border-slate-200/80 dark:border-slate-800 space-y-2">
                <div class="flex items-center gap-2 text-slate-900 dark:text-white font-bold text-sm">
                    <div class="p-1 rounded-lg bg-blue-500 text-white">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <span>1. Pembayaran Instan Menggunakan QRIS Dinamis</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed pl-7">
                    Setiap tagihan diterbitkan dengan kode <strong>QRIS Standar Nasional</strong> yang unik dan dinamis. Anda dapat melakukan pembayaran melalui aplikasi <strong>M-Banking</strong> (BCA, Mandiri, BRI, BNI, BSI, Permata, CIMB, dll.) maupun <strong>E-Wallet</strong> (GoPay, OVO, DANA, ShopeePay, LinkAja).
                </p>
            </div>

            <!-- Highlight Box: Verifikasi Otomatis -->
            <div class="p-4 rounded-xl bg-gradient-to-br from-slate-50 to-emerald-50/40 dark:from-slate-800/40 dark:to-emerald-950/20 border border-slate-200/80 dark:border-slate-800 space-y-2">
                <div class="flex items-center gap-2 text-slate-900 dark:text-white font-bold text-sm">
                    <div class="p-1 rounded-lg bg-emerald-500 text-white">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span>2. Verifikasi Realtime — Tanpa Unggah Struk Manual</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed pl-7">
                    Setelah proses scan dan bayar berhasil di aplikasi perbankan Anda, status transaksi akan <strong>langsung terverifikasi lunas secara otomatis dalam hitungan detik</strong>. Anda tidak perlu lagi melakukan konfirmasi manual melalui pesan ataupun mengunggah bukti transfer.
                </p>
            </div>

            <!-- Highlight Box: Rincian Tarif & Diskon Member -->
            <div class="p-4 rounded-xl bg-gradient-to-br from-slate-50 to-indigo-50/40 dark:from-slate-800/40 dark:to-indigo-950/20 border border-slate-200/80 dark:border-slate-800 space-y-2">
                <div class="flex items-center gap-2 text-slate-900 dark:text-white font-bold text-sm">
                    <div class="p-1 rounded-lg bg-indigo-500 text-white">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <span>3. Transparansi Tarif & Potongan Member CIB</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed pl-7">
                    Rincian tagihan diperhitungkan secara transparan berdasarkan kategori naskah, jumlah penulis, serta opsi DOI. Bagi Anda yang terdaftar sebagai <strong>Member CIB</strong>, sistem secara otomatis mengaplikasikan potongan harga khusus pada setiap naskah yang diajukan.
                </p>
            </div>

            <!-- Video Tutorial Notice -->
            <div class="p-4 rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50/60 dark:from-slate-800/60 dark:to-indigo-950/40 border border-blue-200/80 dark:border-indigo-800/60 flex items-center gap-3.5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-xs text-slate-900 dark:text-white uppercase tracking-wider mb-0.5 flex items-center gap-1.5">
                        <span>Video Panduan & Tutorial:</span>
                    </h4>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Untuk langkah pembayarannya bisa ditonton melalui <strong>video tutorial</strong> pada tombol <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-md bg-white dark:bg-slate-700 text-blue-600 dark:text-blue-300 border border-blue-200 dark:border-slate-600 shadow-xs"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Tutorial</span> di <strong>pojok kanan atas sistem</strong>.
                    </p>
                </div>
            </div>

        </div>

        <!-- Footer Actions -->
        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/40 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2 select-none text-xs text-slate-600 dark:text-slate-400">
                <input type="checkbox" 
                    id="cib-dont-show-announcement"
                    x-model="dontShowAgain" 
                    @change="
                        if ($event.target.checked) {
                            localStorage.setItem('cib_payment_announcement_seen_v1', 'true');
                        } else {
                            localStorage.removeItem('cib_payment_announcement_seen_v1');
                        }
                    "
                    class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 cursor-pointer">
                <label for="cib-dont-show-announcement" class="cursor-pointer">
                    Jangan tampilkan pengumuman ini lagi
                </label>
            </div>

            <button type="button" 
                @click="closeModal()" 
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30 transition-all duration-150 active:scale-[0.98]">
                <span>Saya Mengerti, Lanjutkan</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>

    </div>
</div>
