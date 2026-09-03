<div class="space-y-4 text-xs">
    {{-- BASIC DETAILS --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700">
        <div>
            <span class="text-[10px] uppercase font-bold text-gray-400">Order ID Midtrans</span>
            <div class="font-mono font-bold text-primary-600 dark:text-primary-400 mt-0.5">{{ $record->order_id }}</div>
        </div>
        <div>
            <span class="text-[10px] uppercase font-bold text-gray-400">Waktu Bayar</span>
            <div class="font-semibold text-gray-800 dark:text-gray-200 mt-0.5">
                {{ $record->paid_at ? \Carbon\Carbon::parse($record->paid_at)->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}
            </div>
        </div>
        <div>
            <span class="text-[10px] uppercase font-bold text-gray-400">Status Pembayaran</span>
            <div class="mt-0.5">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                    {{ strtoupper($record->payment_status) }}
                </span>
            </div>
        </div>
        <div>
            <span class="text-[10px] uppercase font-bold text-gray-400">Tipe Transaksi</span>
            <div class="font-semibold mt-0.5 text-blue-600">
                {{ match ($record->type) {
                    'bulk_submission' => 'Kolektif (' . count($record->items) . ' Naskah)',
                    'doi_addon' => 'Add-on DOI',
                    default => 'Publikasi Tunggal',
                } }}
            </div>
        </div>
    </div>

    {{-- PAYER DETAILS --}}
    <div class="p-3 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 flex justify-between items-center text-xs">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">Identitas Pembayar</span>
            <span class="font-bold text-gray-900 dark:text-white">{{ $record->payer_name ?: ($record->user?->name ?? 'Author') }}</span>
            <span class="text-gray-500 dark:text-gray-400 text-[11px] ml-1">({{ $record->payer_email ?: ($record->user?->email ?? '-') }})</span>
        </div>
        @if($record->invoice_number)
            <div class="text-right">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">No. Invoice</span>
                <span class="font-mono font-bold text-primary-600">{{ $record->invoice_number }}</span>
            </div>
        @endif
    </div>

    {{-- ITEMS BREAKDOWN --}}
    @if($record->items->isNotEmpty())
        <div class="p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 space-y-2">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">
                Daftar Naskah Dalam Transaksi ({{ count($record->items) }} Item)
            </span>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($record->items as $idx => $item)
                    @php $sub = $item->submission; @endphp
                    <div class="py-2 flex justify-between items-start gap-3">
                        <div class="space-y-0.5">
                            <div class="font-bold text-gray-900 dark:text-white text-xs">
                                {{ $idx + 1 }}. {{ $sub?->title ?: ($item->item_name ?: 'Naskah Publikasi') }}
                            </div>
                            <div class="text-[11px] text-gray-500">
                                <span>Target: <strong>{{ $sub?->journal?->name ?? 'Jurnal CIB' }}</strong></span>
                                <span class="mx-1">•</span>
                                <span>Penulis: {{ $sub?->author_name ?: '-' }}</span>
                            </div>
                        </div>
                        <div class="text-right font-mono font-bold text-gray-900 dark:text-white text-xs shrink-0">
                            Rp {{ number_format($item->gross_amount, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- FINANCIAL BREAKDOWN --}}
    <div class="p-4 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700">
        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-3">Rincian Perhitungan Bagi Hasil (Settlement)</span>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
            <div class="p-2.5 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
                <span class="text-[10px] font-bold uppercase text-gray-400">Total Kotor (Gross)</span>
                <div class="text-base font-black font-mono mt-1 text-gray-900 dark:text-white">
                    Rp {{ number_format($record->gross_amount, 0, ',', '.') }}
                </div>
            </div>
            <div class="p-2.5 bg-amber-50/60 dark:bg-amber-950/30 rounded-xl border border-amber-200 dark:border-amber-800">
                <span class="text-[10px] font-bold uppercase text-amber-600">MDR QRIS (0.7%)</span>
                <div class="text-base font-black font-mono mt-1 text-amber-600 dark:text-amber-400">
                    Rp {{ number_format($record->mdr_amount, 0, ',', '.') }}
                </div>
            </div>
            <div class="p-2.5 bg-emerald-50/60 dark:bg-emerald-950/30 rounded-xl border border-emerald-200 dark:border-emerald-800">
                <span class="text-[10px] font-bold uppercase text-emerald-700 dark:text-emerald-400">Cut Dev (Net)</span>
                <div class="text-base font-black font-mono mt-1 text-emerald-700 dark:text-emerald-300">
                    Rp {{ number_format($record->developer_net_share, 0, ',', '.') }}
                </div>
            </div>
            <div class="p-2.5 bg-blue-50/60 dark:bg-blue-950/30 rounded-xl border border-blue-200 dark:border-blue-800">
                <span class="text-[10px] font-bold uppercase text-blue-700 dark:text-blue-400">Cut Admin / Jurnal</span>
                <div class="text-base font-black font-mono mt-1 text-blue-700 dark:text-blue-300">
                    Rp {{ number_format($record->journal_share, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="mt-3 p-2 bg-gray-100 dark:bg-gray-900 rounded-lg text-center font-mono text-[11px] text-gray-600 dark:text-gray-400">
            Total Kotor (Rp {{ number_format($record->gross_amount, 0, ',', '.') }}) = 
            MDR (Rp {{ number_format($record->mdr_amount, 0, ',', '.') }}) + 
            Dev Net (Rp {{ number_format($record->developer_net_share, 0, ',', '.') }}) + 
            Admin (Rp {{ number_format($record->journal_share, 0, ',', '.') }})
        </div>
    </div>
</div>
