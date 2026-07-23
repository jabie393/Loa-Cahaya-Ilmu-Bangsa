<div x-data="{ isOpen: true }" x-show="isOpen" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto px-4 py-2 sm:py-6 md:px-8" role="dialog"
    aria-modal="true" aria-labelledby="modal-title">
    <!-- Premium Backdrop with blur and dark tint -->
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
        class="relative w-full max-w-lg flex flex-col transform overflow-hidden rounded-2xl bg-white/95 px-4 py-4 sm:px-6 sm:py-5 text-left shadow-[0_25px_60px_-15px_rgba(16,185,129,0.2)] transition-all dark:bg-slate-900/95 border border-emerald-500/10 dark:border-emerald-500/20 backdrop-blur-xl">
        
        <!-- Ambient glows -->
        <div class="pointer-events-none absolute -left-20 -top-20 h-40 w-40 rounded-full bg-emerald-500/10 blur-[60px] dark:bg-emerald-500/5"></div>
        <div class="pointer-events-none absolute -right-20 -bottom-20 h-40 w-40 rounded-full bg-amber-500/10 blur-[60px] dark:bg-amber-500/5"></div>

        <!-- Header -->
        <div class="relative z-10 flex items-center justify-between border-b border-slate-100 pb-2 sm:pb-3 dark:border-slate-800/80 flex-shrink-0">
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-lg sm:rounded-xl bg-gradient-to-br from-amber-500/20 to-orange-500/20 text-amber-600 dark:text-amber-400 shadow-sm">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <div>
                    <h3 id="modal-title" class="text-sm sm:text-base font-extrabold tracking-tight text-slate-800 dark:text-slate-100">
                        Pengingat Penting!
                    </h3>
                    <p class="text-[9px] sm:text-[10px] font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider mt-0.5">
                        Kesesuaian Template Naskah
                    </p>
                </div>
            </div>
            <button @click="isOpen = false" class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none dark:text-slate-500 dark:hover:bg-slate-800/80 dark:hover:text-slate-300 transition-all duration-300 transform hover:rotate-90">
                <svg class="h-4.5 w-4.5 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Body Content -->
        <div class="relative z-10 flex-1 pr-1 py-2 sm:py-4 text-slate-600 dark:text-slate-300 text-xs sm:text-sm">
            <div class="mb-2 sm:mb-4 p-2.5 sm:p-3.5 rounded-xl bg-gradient-to-r from-emerald-500/10 to-amber-500/10 border border-emerald-500/10">
                <span class="font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 block mb-0.5 sm:mb-1">Apakah artikel Anda sudah sesuai template?</span>
                Sebelum mengirimkan pengajuan, mohon pastikan berkas naskah yang Anda unggah telah disesuaikan sepenuhnya dengan template resmi jurnal target yang Anda pilih.
            </div>

            <div class="space-y-2 sm:space-y-4">
                <div class="flex gap-2 sm:gap-3">
                    <div class="flex-shrink-0 mt-0.5 text-emerald-500">
                        <svg class="h-4 sm:h-5 w-4 sm:w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 block">Gunakan Template Resmi</span>
                        Unduh template jurnal target terlebih dahulu. Sesuaikan seluruh format tulisan (judul, abstrak, heading, daftar pustaka) ke dalam template tersebut.
                    </div>
                </div>

                <div class="flex gap-2 sm:gap-3">
                    <div class="flex-shrink-0 mt-0.5 text-emerald-500">
                        <svg class="h-4 sm:h-5 w-4 sm:w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 block">Unggah Naskah PDF Digital</span>
                        Berkas harus berupa PDF digital asli (teks harus bisa diseleksi/diblok, bukan file gambar hasil scan atau foto kamera).
                    </div>
                </div>

                <div class="flex gap-2 sm:gap-3">
                    <div class="flex-shrink-0 mt-0.5 text-amber-500">
                        <svg class="h-4 sm:h-5 w-4 sm:w-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <span class="font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-100 block">Verifikasi Validitas Template</span>
                        Sistem kami menerapkan pemindaian otomatis. Pengunggahan berkas yang tidak sesuai template (seperti skripsi utuh atau format jurnal lain) akan <span class="text-amber-600 dark:text-amber-400 font-bold">ditolak secara otomatis</span> saat Anda menekan tombol Submit.
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="relative z-10 flex flex-col sm:flex-row items-center justify-end gap-3 border-t border-slate-100 pt-2.5 sm:pt-3 dark:border-slate-800/80 flex-shrink-0">
            <button @click="isOpen = false"
                class="w-full sm:w-auto relative inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-emerald-600 to-amber-500 px-4 py-2 sm:px-5 sm:py-2.5 text-xs font-bold text-white shadow-[0_3px_10px_rgba(16,185,129,0.2)] hover:shadow-[0_4px_12px_rgba(16,185,129,0.25)] hover:scale-[1.01] focus:outline-none transition-all duration-300 transform active:scale-100">
                Saya Mengerti, Lanjutkan
            </button>
        </div>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>