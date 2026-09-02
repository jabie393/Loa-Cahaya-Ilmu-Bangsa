<div class="space-y-4 text-xs">
    {{-- BASIC DETAILS --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700">
        <div>
            <span class="text-[10px] uppercase font-bold text-gray-400">Kode Transaksi</span>
            <div class="font-mono font-bold text-primary-600 dark:text-primary-400 mt-0.5">TRX-{{ $record->id }}</div>
        </div>
        <div>
            <span class="text-[10px] uppercase font-bold text-gray-400">Waktu Bayar</span>
            <div class="font-semibold text-gray-800 dark:text-gray-200 mt-0.5">{{ $record->created_at?->format('d M Y, H:i') ?? '-' }}</div>
        </div>
        <div>
            <span class="text-[10px] uppercase font-bold text-gray-400">Status</span>
            <div class="mt-0.5">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                    {{ strtoupper($record->status) }}
                </span>
            </div>
        </div>
        <div>
            <span class="text-[10px] uppercase font-bold text-gray-400">Pilihan DOI</span>
            <div class="font-semibold mt-0.5 {{ $record->has_doi ? 'text-emerald-600' : 'text-gray-500' }}">
                {{ $record->has_doi ? 'Dengan DOI' : 'Tanpa DOI' }}
            </div>
        </div>
    </div>

    {{-- ARTICLE DETAILS --}}
    <div class="p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 space-y-2">
        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Judul Naskah & Jurnal</span>
        <div class="text-sm font-bold text-gray-900 dark:text-white leading-snug">
            {{ $record->title }}
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">
            <strong>Penulis:</strong> {{ $record->author_name }} 
            @if(is_array($record->authors) && count($record->authors) > 0)
                ({{ count($record->authors) }} Penulis)
            @endif
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">
            <strong>Jurnal:</strong> {{ $record->journal?->name ?? '-' }}
        </div>
    </div>

    {{-- FINANCIAL BREAKDOWN --}}
    <div class="p-4 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700">
        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-3">Rincian Perhitungan Potongan Bagi Hasil</span>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
            <div class="p-2.5 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
                <span class="text-[10px] font-bold uppercase text-gray-400">Total Kotor</span>
                <div class="text-base font-black font-mono mt-1 text-gray-900 dark:text-white">
                    Rp {{ number_format($record->gross_price, 0, ',', '.') }}
                </div>
            </div>
            <div class="p-2.5 bg-amber-50/60 dark:bg-amber-950/30 rounded-xl border border-amber-200 dark:border-amber-800">
                <span class="text-[10px] font-bold uppercase text-amber-600">MDR QRIS (0.7%)</span>
                <div class="text-base font-black font-mono mt-1 text-amber-600 dark:text-amber-400">
                    Rp {{ number_format($record->qris_fee, 0, ',', '.') }}
                </div>
            </div>
            <div class="p-2.5 bg-emerald-50/60 dark:bg-emerald-950/30 rounded-xl border border-emerald-200 dark:border-emerald-800">
                <span class="text-[10px] font-bold uppercase text-emerald-700 dark:text-emerald-400">Cut Developer</span>
                <div class="text-base font-black font-mono mt-1 text-emerald-700 dark:text-emerald-300">
                    Rp {{ number_format($record->dev_cut, 0, ',', '.') }}
                </div>
            </div>
            <div class="p-2.5 bg-blue-50/60 dark:bg-blue-950/30 rounded-xl border border-blue-200 dark:border-blue-800">
                <span class="text-[10px] font-bold uppercase text-blue-700 dark:text-blue-400">Cut Admin CIB</span>
                <div class="text-base font-black font-mono mt-1 text-blue-700 dark:text-blue-300">
                    Rp {{ number_format($record->admin_cut, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="mt-3 p-2 bg-gray-100 dark:bg-gray-900 rounded-lg text-center font-mono text-[11px] text-gray-600 dark:text-gray-400">
            Rumus: Total Kotor (Rp {{ number_format($record->gross_price, 0, ',', '.') }}) = 
            QRIS (Rp {{ number_format($record->qris_fee, 0, ',', '.') }}) + 
            Dev (Rp {{ number_format($record->dev_cut, 0, ',', '.') }}) + 
            Admin (Rp {{ number_format($record->admin_cut, 0, ',', '.') }})
        </div>
    </div>

    {{-- PAYMENT PROOF PREVIEW --}}
    @if($record->proof_of_payment)
        <div class="p-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl flex items-center justify-between">
            <span class="font-bold text-gray-700 dark:text-gray-300">Bukti Pembayaran / Slip QRIS:</span>
            <a href="{{ Storage::disk('public')->url($record->proof_of_payment) }}" target="_blank" class="text-primary-600 font-bold hover:underline">
                Buka File Bukti Bayar ↗
            </a>
        </div>
    @endif
</div>
