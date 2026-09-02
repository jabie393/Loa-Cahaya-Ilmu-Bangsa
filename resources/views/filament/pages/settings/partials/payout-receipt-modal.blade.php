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
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                STATUS: {{ strtoupper($record->status) }}
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
        <div class="flex justify-between">
            <span class="text-gray-400">Catatan / Periode:</span>
            <span class="font-bold text-gray-800 dark:text-gray-200 text-right">{{ $record->notes ?: '-' }}</span>
        </div>
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
