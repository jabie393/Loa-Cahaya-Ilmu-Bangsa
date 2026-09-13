@php
    $statusConfig = match ($record->status) {
        'waiting_confirmation' => [
            'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800',
            'label' => 'MENUNGGU KONFIRMASI DEV',
        ],
        'confirmed' => [
            'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800',
            'label' => 'DIKONFIRMASI DITERIMA',
        ],
        'completed' => [
            'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800',
            'label' => 'SELESAI',
        ],
        'rejected' => [
            'class' => 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300 border border-red-300 dark:border-red-800',
            'label' => 'DITOLAK / BELUM MASUK',
        ],
        default => [
            'class' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
            'label' => strtoupper($record->status),
        ],
    };
@endphp

<div class="space-y-3.5 sm:space-y-4">
    {{-- TOP RECEIPT HEADER CARD --}}
    <div class="text-center p-4 sm:p-5 bg-gradient-to-b from-gray-50 to-gray-100/60 dark:from-gray-800/80 dark:to-gray-900/90 rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-xs">
        <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 dark:text-gray-400 block">
            Bukti Mutasi Pembayaran Developer
        </span>
        <div class="text-base sm:text-lg font-mono font-black text-primary-600 dark:text-primary-400 mt-1 tracking-wide">
            {{ $record->payout_no }}
        </div>
        <div class="text-2xl sm:text-3xl font-black font-mono text-emerald-600 dark:text-emerald-400 my-2 tracking-tight">
            Rp {{ number_format($record->amount, 0, ',', '.') }}
        </div>
        <div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10.5px] font-bold tracking-wider {{ $statusConfig['class'] }}">
                STATUS: {{ $statusConfig['label'] }}
            </span>
        </div>
    </div>

    {{-- DETAILS CARD --}}
    <div class="p-4 sm:p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 space-y-3 font-mono text-xs shadow-xs">
        <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800/80 pb-2.5">
            <span class="text-gray-400 font-sans text-xs">Waktu Pencairan:</span>
            <span class="font-bold text-gray-800 dark:text-gray-200 text-right">{{ $record->created_at?->format('d F Y, H:i') ?? '-' }}</span>
        </div>
        <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800/80 pb-2.5">
            <span class="text-gray-400 font-sans text-xs">No. Referensi:</span>
            <span class="font-bold text-primary-600 dark:text-primary-400 text-right break-all">{{ $record->reference_no ?: '-' }}</span>
        </div>
        <div class="flex justify-between items-start gap-4 {{ $record->rejection_reason ? 'border-b border-gray-100 dark:border-gray-800/80 pb-2.5' : '' }}">
            <span class="text-gray-400 font-sans text-xs shrink-0">Catatan / Periode:</span>
            <span class="font-bold text-gray-800 dark:text-gray-200 text-right">{{ $record->notes ?: '-' }}</span>
        </div>

        @if($record->rejection_reason)
            <div class="flex justify-between items-start gap-4 text-red-600 dark:text-red-400 pt-1">
                <span class="font-bold font-sans text-xs shrink-0">Alasan Penolakan:</span>
                <span class="font-bold text-right">{{ $record->rejection_reason }}</span>
            </div>
        @endif
    </div>

    @if($record->proof_file)
        <div class="p-3.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl flex items-center justify-between shadow-xs">
            <span class="font-bold text-xs text-gray-700 dark:text-gray-300">File Slip Bukti Transfer:</span>
            <a href="{{ Storage::disk('public')->url($record->proof_file) }}" target="_blank" class="text-primary-600 dark:text-primary-400 text-xs font-bold hover:underline inline-flex items-center gap-1">
                <span>Buka Slip Transfer</span>
                <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" class="w-3.5 h-3.5" />
            </a>
        </div>
    @endif
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(100, 116, 139, 0.2);
        border-radius: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(100, 116, 139, 0.4);
    }
</style>
