<x-filament-panels::page>
    <style>
        /* Specific only to table action buttons to prevent dimming on wire:poll without breaking header buttons/modals */
        .fi-ta-actions button,
        .fi-ta-actions a {
            opacity: 1 !important;
        }

        .fi-ta-row [wire\:loading] {
            opacity: 1 !important;
        }

        /* Prevent table action dropdown from being clipped or flipping into navbar when few records exist */
        .fi-ta-content-ctn {
            min-height: 480px;
        }

        .fi-dropdown-panel {
            z-index: 50 !important;
        }
    </style>


    <!-- Payment Method Announcement Banner (Allows re-opening anytime) -->
    <div class="mb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-3.5 rounded-2xl bg-gradient-to-r from-blue-500/10 via-indigo-500/5 to-sky-500/10 border border-blue-500/15 dark:border-blue-500/20 backdrop-blur-sm">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500/20 to-indigo-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 shadow-sm border border-blue-500/20">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent dark:from-blue-400 dark:to-indigo-400 font-extrabold">Pembaruan Metode Pembayaran QRIS Dinamis</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/15 text-blue-600 dark:text-blue-400 border border-blue-500/30">Baru</span>
                </div>
                <div class="text-xs text-slate-600 dark:text-slate-400">
                    Pembayaran tagihan kini terverifikasi otomatis dalam hitungan detik tanpa perlu upload bukti transfer.
                </div>
            </div>
        </div>
        <button type="button" 
            x-data 
            @click="$dispatch('open-payment-announcement')" 
            class="shrink-0 px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold shadow-[0_2px_8px_rgba(37,99,235,0.25)] transition-all duration-150 flex items-center gap-1.5 active:scale-95">
            <span>Pelajari Pembaruan</span>
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    <!-- Render the default table -->
    {{ $this->table }}

    <!-- Announcement Modal Popup -->
    @include('filament.submissions.payment-announcement-modal')
</x-filament-panels::page>