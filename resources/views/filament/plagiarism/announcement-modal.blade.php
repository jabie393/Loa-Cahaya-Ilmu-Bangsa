<!-- Announcement Modal for Plagiarism Checks Page -->
<div x-data="{ isOpen: false }" x-init="setTimeout(() => isOpen = true, 400)" x-show="isOpen" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto px-4 py-1.5 sm:px-6 sm:py-2.5 md:px-8 md:py-3.5" role="dialog"
    aria-modal="true" aria-labelledby="modal-title">
    <!-- Premium Backdrop with high-performance blur and subtle dark tint -->
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 backdrop-blur-none" x-transition:enter-end="opacity-100 backdrop-blur-md"
        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 backdrop-blur-md"
        x-transition:leave-end="opacity-0 backdrop-blur-none"
        class="fixed inset-0 bg-slate-900/60 transition-opacity dark:bg-slate-950/80" @click="isOpen = false"></div>

    <!-- Modal Card Container -->
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 scale-95"
        class="relative w-fit max-w-[95vw] md:max-w-5xl max-h-[96vh] sm:max-h-[92vh] flex flex-col transform overflow-hidden rounded-2xl bg-white/95 px-3 py-1.5 sm:px-4 sm:py-2 text-left shadow-[0_25px_60px_-15px_rgba(16,185,129,0.15)] transition-all dark:bg-slate-900/95 border border-emerald-500/10 dark:border-emerald-500/20 backdrop-blur-xl">
        <!-- Modern ambient glows behind the card -->
        <div
            class="pointer-events-none absolute -left-40 -top-40 h-80 w-80 rounded-full bg-emerald-500/10 blur-[100px] dark:bg-emerald-500/5">
        </div>
        <div
            class="pointer-events-none absolute -right-40 -bottom-40 h-80 w-80 rounded-full bg-amber-500/10 blur-[100px] dark:bg-amber-500/5">
        </div>

        <!-- Header -->
        <div
            class="relative z-10 flex items-center justify-between border-b border-slate-100 pb-1 dark:border-slate-800/80 flex-shrink-0">
            <div class="flex items-center gap-3">
                <!-- Glowing branding-colored icon -->
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500/10 to-amber-500/10 text-emerald-600 dark:from-emerald-500/20 dark:to-amber-500/20 dark:text-emerald-400 shadow-sm">
                    <svg class="h-6 w-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />
                    </svg>
                </div>
                <div>
                    <h3 id="modal-title"
                        class="text-base font-extrabold tracking-tight sm:text-lg md:text-xl bg-gradient-to-r from-emerald-600 to-amber-500 bg-clip-text text-transparent dark:from-emerald-400 dark:to-amber-400">
                        Kabar Gembira!
                    </h3>
                    <p
                        class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-0.5">
                        Fitur Baru: Parafrase Akademik Otomatis
                    </p>
                </div>
            </div>

            <!-- Sleek interactive close button with hover rotation -->
            <button @click="isOpen = false"
                class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none dark:text-slate-500 dark:hover:bg-slate-800/80 dark:hover:text-slate-300 transition-all duration-300 transform hover:rotate-90"
                aria-label="Tutup">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Body Content (Scrollable & responsive scrollbar) -->
        <div class="relative z-10 flex-1 overflow-y-auto pr-1 custom-scrollbar scroll-smooth">
            <!-- Beautiful High-quality Announcement Image Wrapper -->
            <div
                class="group relative overflow-hidden rounded-xl border border-slate-100 dark:border-slate-800/60 shadow-xl bg-slate-50 dark:bg-slate-950/40 flex items-center justify-center max-h-[64vh] sm:max-h-[72vh]">
                <!-- Smooth Zoom Image -->
                <img src="{{ asset('assets/cek-plagiasi.jpeg') }}" alt="Panduan Parafrase Cek Plagiasi"
                    class="max-w-full max-h-[64vh] sm:max-h-[72vh] object-contain transition-transform duration-700 ease-out group-hover:scale-[1.012]" />

                <!-- Premium Hover Highlight Overlay -->
                <div
                    class="pointer-events-none absolute inset-0 bg-gradient-to-t from-emerald-950/10 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                </div>
            </div>
        </div>

        <!-- Footer / Interactive Actions -->
        <div
            class="relative z-10 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-100 pt-1 dark:border-slate-800/80 flex-shrink-0">
            <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                <span class="inline-block h-2 w-2 rounded-full bg-emerald-500 animate-ping"></span>
                Loa Cahaya Ilmu Bangsa
            </span>
            <button @click="isOpen = false"
                class="w-full sm:w-auto relative inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-emerald-600 to-amber-500 px-4 py-2 text-xs font-bold text-white shadow-[0_3px_10px_rgba(16,185,129,0.2)] hover:shadow-[0_4px_12px_rgba(16,185,129,0.25)] hover:scale-[1.01] focus:outline-none transition-all duration-300 transform active:scale-100">
                Saya Mengerti, Mulai Pengecekan
            </button>
        </div>
    </div>
</div>

<!-- Essential Styles for FOUC and custom utilities -->
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
        background: rgba(16, 185, 129, 0.2);
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(16, 185, 129, 0.4);
    }

    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(16, 185, 129, 0.3);
    }

    .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(16, 185, 129, 0.5);
    }
</style>