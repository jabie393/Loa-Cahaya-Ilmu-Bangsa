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

<div class="space-y-4 text-xs">
    <div class="text-center p-4 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700">
        <span class="text-[10px] uppercase font-bold text-gray-400">Bukti Mutasi Pembayaran Developer</span>
        <div class="text-xl font-mono font-black text-primary-600 dark:text-primary-400 mt-1">
            {{ $record->payout_no }}
        </div>
        <div class="text-2xl font-black font-mono text-emerald-700 dark:text-emerald-300 mt-2">
            Rp {{ number_format($record->amount, 0, ',', '.') }}
        </div>
        <div class="mt-2">
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $statusConfig['class'] }}">
                STATUS: {{ $statusConfig['label'] }}
            </span>
        </div>
    </div>

    <div class="p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 space-y-2.5 font-mono">
        <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
            <span class="text-gray-400">Waktu Pencairan:</span>
            <span class="font-bold text-gray-800 dark:text-gray-200">{{ $record->created_at?->format('d F Y, H:i') ?? '-' }}</span>
        </div>
        <div class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
            <span class="text-gray-400">No. Referensi:</span>
            <span class="font-bold text-gray-800 dark:text-gray-200">{{ $record->reference_no ?: '-' }}</span>
        </div>
        <div class="flex justify-between {{ $record->rejection_reason ? 'border-b border-gray-100 dark:border-gray-800 pb-2' : '' }}">
            <span class="text-gray-400">Catatan / Periode:</span>
            <span class="font-bold text-gray-800 dark:text-gray-200 text-right">{{ $record->notes ?: '-' }}</span>
        </div>

        @if($record->rejection_reason)
            <div class="flex justify-between text-red-600 dark:text-red-400 pt-1">
                <span class="font-bold">Alasan Penolakan:</span>
                <span class="font-bold text-right">{{ $record->rejection_reason }}</span>
            </div>
        @endif
    </div>

    @if($record->proof_file)
        <div class="p-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl flex items-center justify-between">
            <span class="font-bold text-gray-700 dark:text-gray-300">File Slip Bukti Transfer:</span>
            <a href="{{ Storage::disk('public')->url($record->proof_file) }}" target="_blank" class="text-primary-600 font-bold hover:underline">
                Buka Slip Transfer ↗
            </a>
        </div>
    @endif
</div>
