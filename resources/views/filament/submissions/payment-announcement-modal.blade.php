<!-- Announcement Modal for Submissions Page: Payment Method Update -->
<div x-data="{
    isOpen: false,
    dontShowAgain: false,
    init() {
        this.dontShowAgain = localStorage.getItem('cib_payment_announcement_seen_v1') === 'true';
        if (!this.dontShowAgain) {
            setTimeout(() => {
                this.isOpen = true;
            }, 400);
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
    dontShowAgain = localStorage.getItem('cib_payment_announcement_seen_v1') === 'true';
"
x-show="isOpen" 
x-cloak
class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto px-4 py-1.5 sm:px-6 sm:py-2.5 md:px-8 md:py-3.5" 
role="dialog"
aria-modal="true" 
aria-labelledby="modal-title">

    <!-- Premium Backdrop with high-performance blur and subtle dark tint -->
    <div x-show="isOpen" 
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 backdrop-blur-none" 
        x-transition:enter-end="opacity-100 backdrop-blur-md"
        x-transition:leave="transition ease-in duration-300" 
        x-transition:leave-start="opacity-100 backdrop-blur-md"
        x-transition:leave-end="opacity-0 backdrop-blur-none"
        class="fixed inset-0 bg-slate-900/60 transition-opacity dark:bg-slate-950/80" 
        @click="closeModal()"></div>

    <!-- Modal Card Container (Border soft with ambient glow, consistent with welcome-modal) -->
    <div x-show="isOpen" 
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 scale-95"
        style="border: none !important; outline: none !important;"
        class="relative w-full max-w-2xl sm:max-w-3xl max-h-[96vh] sm:max-h-[92vh] flex flex-col transform overflow-hidden rounded-2xl bg-white/95 px-4 py-3 sm:px-6 sm:py-4 text-left shadow-[0_25px_60px_-15px_rgba(37,99,235,0.15)] transition-all dark:bg-slate-900/95 border-0 backdrop-blur-xl">
        
        <!-- Modern ambient glows behind the card -->
        <div class="pointer-events-none absolute -left-40 -top-40 h-80 w-80 rounded-full bg-blue-500/10 blur-[100px] dark:bg-blue-500/5"></div>
        <div class="pointer-events-none absolute -right-40 -bottom-40 h-80 w-80 rounded-full bg-indigo-500/10 blur-[100px] dark:bg-indigo-500/5"></div>

        <!-- Header -->
        <div class="relative z-10 flex items-center justify-between border-b border-slate-100 pb-2.5 dark:border-slate-800/80 flex-shrink-0">
            <div class="flex items-center gap-3">
                <!-- Glowing branding-colored icon -->
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500/10 to-indigo-500/10 text-blue-600 dark:from-blue-500/20 dark:to-indigo-500/20 dark:text-blue-400 shadow-sm border border-blue-500/20">
                    <svg class="h-6 w-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </div>
                <div>
                    <h3 id="modal-title" class="text-base font-extrabold tracking-tight sm:text-lg md:text-xl bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent dark:from-blue-400 dark:to-indigo-400">
                        Pembaruan Sistem Pembayaran
                    </h3>
                    <p class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-0.5">
                        Integrasi QRIS Dinamis & Verifikasi Otomatis
                    </p>
                </div>
            </div>

            <!-- Sleek interactive close button with hover rotation (from welcome-modal) -->
            <button type="button" 
                @click="closeModal()" 
                class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none dark:text-slate-500 dark:hover:bg-slate-800/80 dark:hover:text-slate-300 transition-all duration-300 transform hover:rotate-90"
                aria-label="Tutup">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Body Content (Scrollable & responsive custom-scrollbar) -->
        <div class="relative z-10 flex-1 overflow-y-auto pr-1 py-3 custom-scrollbar scroll-smooth space-y-3">
            
            <!-- Highlight Box 1: Support Seluruh Bank & E-Wallet -->
            <div class="p-3.5 rounded-xl bg-gradient-to-br from-blue-50/80 via-blue-50/40 to-slate-50 dark:from-slate-800/60 dark:to-blue-950/30 border border-blue-100/90 dark:border-slate-800 space-y-1.5 shadow-xs">
                <div class="flex items-center gap-2 text-slate-900 dark:text-white font-bold text-xs sm:text-sm">
                    <div class="p-1.5 rounded-lg bg-blue-600 text-white shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <span>1. Pembayaran Instan Menggunakan QRIS Dinamis</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed pl-7 sm:pl-8">
                    Setiap tagihan diterbitkan dengan kode <strong>QRIS Standar Nasional</strong> yang unik dan dinamis. Anda dapat membayar melalui seluruh aplikasi <strong>M-Banking</strong> (BCA, Mandiri, BRI, BNI, BSI, Permata, CIMB, dll.) maupun <strong>E-Wallet</strong> (GoPay, OVO, DANA, ShopeePay, LinkAja).
                </p>
            </div>

            <!-- Highlight Box 2: Verifikasi Otomatis -->
            <div class="p-3.5 rounded-xl bg-gradient-to-br from-blue-50/80 via-blue-50/40 to-slate-50 dark:from-slate-800/60 dark:to-blue-950/30 border border-blue-100/90 dark:border-slate-800 space-y-1.5 shadow-xs">
                <div class="flex items-center gap-2 text-slate-900 dark:text-white font-bold text-xs sm:text-sm">
                    <div class="p-1.5 rounded-lg bg-blue-600 text-white shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span>2. Verifikasi Realtime — Tanpa Perlu Unggah Struk Manual</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed pl-7 sm:pl-8">
                    Setelah proses scan dan bayar berhasil di aplikasi perbankan Anda, status transaksi akan <strong>langsung terverifikasi lunas secara otomatis dalam hitungan detik</strong>. Anda tidak perlu lagi melakukan konfirmasi manual ataupun mengunggah bukti transfer.
                </p>
            </div>

            <!-- Highlight Box 3: Terintegrasi OJS -->
            <div class="p-3.5 rounded-xl bg-gradient-to-br from-blue-50/80 via-blue-50/40 to-slate-50 dark:from-slate-800/60 dark:to-blue-950/30 border border-blue-100/90 dark:border-slate-800 space-y-1.5 shadow-xs">
                <div class="flex items-center gap-2 text-slate-900 dark:text-white font-bold text-xs sm:text-sm">
                    <div class="p-1.5 rounded-lg bg-blue-600 text-white shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </div>
                    <span>3. Terintegrasi OJS</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed pl-7 sm:pl-8">
                    Bisa pengajuan <strong>DOI</strong> sebelum atau sesudah proses submit, maupun revisi PDF dapat dilakukan pada sistem kurasi digital LOA dan <strong>terintegrasi langsung dengan OJS</strong>.
                </p>
            </div>

            <!-- Video Tutorial Notice (Pojok kanan atas) -->
            <div class="p-3.5 rounded-xl bg-gradient-to-r from-blue-50/90 via-indigo-50/60 to-blue-50/40 dark:from-slate-800/60 dark:to-blue-950/40 border border-blue-200/80 dark:border-blue-800/60 flex items-center gap-3 shadow-xs">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-xs text-slate-900 dark:text-white uppercase tracking-wider mb-0.5">
                        Video Panduan & Tutorial:
                    </h4>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Untuk langkah pembayarannya bisa ditonton melalui <strong>video tutorial</strong> pada tombol <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-md bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 shadow-xs"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Tutorial</span> di <strong>pojok kanan atas sistem</strong>.
                    </p>
                </div>
            </div>

        </div>

        <!-- Footer / Interactive Actions -->
        <div class="relative z-10 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-100 pt-3 dark:border-slate-800/80 flex-shrink-0">
            <div class="flex items-center gap-2 select-none text-xs text-slate-500 dark:text-slate-400">
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
                <label for="cib-dont-show-announcement" class="cursor-pointer font-medium">
                    Jangan tampilkan pengumuman ini lagi
                </label>
            </div>

            <button type="button" 
                @click="closeModal()" 
                class="w-full sm:w-auto relative inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30 hover:scale-[1.01] focus:outline-none transition-all duration-300 transform active:scale-100">
                <span>Saya Mengerti, Lanjutkan</span>
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>

    </div>
</div>

<!-- Essential Styles for FOUC and custom utilities matching welcome-modal -->
<style>
    [x-cloak] {
        display: none !important;
    }

    /* Custom premium slim scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(37, 99, 235, 0.2);
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(37, 99, 235, 0.4);
    }

    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(37, 99, 235, 0.3);
    }

    .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(37, 99, 235, 0.5);
    }
</style>
