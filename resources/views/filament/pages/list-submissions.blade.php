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
    <div class="mb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-gradient-to-r from-blue-500/10 via-indigo-500/10 to-emerald-500/10 border border-blue-500/20 dark:border-blue-500/30">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                    <span>Pembaruan Metode Pembayaran QRIS Dinamis</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">Baru</span>
                </div>
                <div class="text-xs text-slate-600 dark:text-slate-400">
                    Pembayaran tagihan kini terverifikasi otomatis dalam hitungan detik tanpa perlu upload bukti transfer.
                </div>
            </div>
        </div>
        <button type="button" 
            x-data 
            @click="$dispatch('open-payment-announcement')" 
            class="shrink-0 px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition-all duration-150 flex items-center gap-1.5 active:scale-95">
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