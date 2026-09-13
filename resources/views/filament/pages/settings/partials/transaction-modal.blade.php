@php
    $paidAt = $record->paid_at
        ? \Carbon\Carbon::parse($record->paid_at)->translatedFormat('d M Y, H:i') . ' WIB'
        : ($record->created_at ? \Carbon\Carbon::parse($record->created_at)->translatedFormat('d M Y, H:i') . ' WIB' : '-');

    $transType = match ($record->type) {
        'bulk_submission' => 'Kolektif (' . count($record->items) . ' Naskah)',
        'doi_addon' => 'Add-on DOI Resmi',
        default => 'Publikasi Tunggal',
    };

    $payerUser = $record->user ?? $record->submission?->user;
    $payerName = $payerUser?->name ?: ($record->payer_name ?: 'Author');
    $payerEmail = $payerUser?->email ?: ($record->payer_email ?: '-');

    // Initials for avatar circle (e.g. "TU" for "Test User")
    $words = preg_split('/\s+/', trim($payerName));
    $initials = '';
    foreach (array_slice($words, 0, 2) as $w) {
        $initials .= strtoupper(mb_substr($w, 0, 1));
    }
    $initials = $initials ?: 'TU';

    // Invoice number
    $invoiceDate = $record->paid_at
        ? \Carbon\Carbon::parse($record->paid_at)->format('Ymd')
        : ($record->created_at ? \Carbon\Carbon::parse($record->created_at)->format('Ymd') : date('Ymd'));
    $invoiceNo = $record->invoice_number ?: ('INV/CIB/' . $invoiceDate . '/' . sprintf('%04d', $record->id));

    $isPaid = in_array(strtolower($record->payment_status), ['paid', 'settlement', 'success', 'capture']);

    $hasItems = $record->items->isNotEmpty();
    $itemCount = $hasItems ? count($record->items) : 1;

    $gateway = strtolower($record->gateway ?? '');
    if (empty($gateway)) {
        if (str_starts_with($record->order_id, 'BYR-') || str_contains($record->order_id, 'BELIBAYAR')) {
            $gateway = 'belibayar';
        } else {
            $gateway = 'midtrans';
        }
    }
    $gatewayLabel = match($gateway) {
        'belibayar' => 'Belibayar',
        default => 'Midtrans',
    };
@endphp

<div class="space-y-3.5 sm:space-y-4 max-h-[78vh] overflow-y-auto custom-scrollbar pr-0.5">
    {{-- TOP DARK NAVY CARD (COMPACT & SLEEK BANNER) --}}
    <div class="bg-gradient-to-r from-[#0d183f] via-[#0f1d4a] to-[#12245c] text-white rounded-2xl p-3.5 sm:p-4 shadow-md relative overflow-hidden border border-blue-900/40">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
            <div class="space-y-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-200/70">
                        Order ID {{ $gatewayLabel }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono tracking-wider {{ $isPaid ? 'bg-[#09262b] border border-emerald-500/40 text-emerald-400' : 'bg-amber-950/80 border border-amber-500/40 text-amber-400' }}">
                        {{ strtoupper($record->payment_status ?: 'PAID') }}
                    </span>
                </div>
                <div class="font-mono font-extrabold text-base sm:text-lg text-white tracking-wide break-all">
                    {{ $record->order_id }}
                </div>
            </div>

            <div class="flex items-center gap-4 sm:gap-6 pt-2 sm:pt-0 border-t sm:border-t-0 border-blue-900/60 shrink-0 text-left sm:text-right">
                <div>
                    <span class="text-[9.5px] font-bold uppercase tracking-wider text-blue-200/60 block">
                        Waktu Bayar
                    </span>
                    <div class="text-xs sm:text-sm font-semibold font-mono text-white mt-0.5">
                        {{ $paidAt }}
                    </div>
                </div>
                <div class="border-l border-blue-800/60 pl-4 sm:pl-6">
                    <span class="text-[9.5px] font-bold uppercase tracking-wider text-blue-200/60 block">
                        Tipe Transaksi
                    </span>
                    <div class="text-xs sm:text-sm font-semibold text-white mt-0.5">
                        {{ $transType }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TWO COLUMN LAYOUT --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 sm:gap-4 items-start">
        {{-- LEFT COLUMN (7 COLS): DAFTAR NASKAH (SUPPORTS MULTI-ITEM) --}}
        <div class="lg:col-span-7 space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-[10.5px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Daftar Naskah ({{ $itemCount }} {{ $itemCount > 1 ? 'Items' : 'Item' }})
                </span>
                @if($itemCount > 1)
                    <span class="text-[9.5px] font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-900 px-2 py-0.5 rounded-full">
                        Transaksi Kolektif
                    </span>
                @endif
            </div>

            {{-- SCROLLABLE ITEMS LIST --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-3 divide-y divide-gray-100 dark:divide-gray-800 max-h-[200px] sm:max-h-[240px] overflow-y-auto custom-scrollbar space-y-1">
                @if($hasItems)
                    @foreach($record->items as $idx => $item)
                        @php
                            $sub = $item->submission;
                            $wantsDoi = false;
                            $itemType = null;
                            if ($sub) {
                                $wantsDoi = (bool) ($sub->want_doi || $sub->has_doi);
                                try {
                                    $pricing = app(\App\Services\SubmissionPricingService::class)->calculate($sub);
                                    $itemType = $pricing['tier_name'] ?? null;
                                } catch (\Throwable $e) {
                                    $itemType = null;
                                }
                            }
                            if (empty($itemType)) {
                                if (($item->item_type ?? null) === 'doi_addon') {
                                    $itemType = 'Add-on DOI Resmi';
                                    $wantsDoi = true;
                                } else {
                                    $itemType = $item->item_name ?: 'Publikasi Naskah';
                                }
                            }
                        @endphp
                        <div class="py-2 first:pt-0.5 last:pb-0.5 flex justify-between items-start gap-3">
                            <div class="space-y-1 min-w-0 flex-1">
                                {{-- ITEM TYPE & DOI REQUEST BADGE --}}
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-bold text-gray-900 dark:text-white text-xs sm:text-sm">
                                        {{ $itemType }}
                                    </span>
                                    @if($wantsDoi)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9.5px] font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800">
                                            <x-filament::icon icon="heroicon-m-check-badge" class="w-3 h-3 text-blue-600 dark:text-blue-400" />
                                            Request DOI
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9.5px] font-medium bg-gray-100 text-gray-500 border border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700">
                                            Tanpa DOI
                                        </span>
                                    @endif
                                </div>

                                {{-- TITLE --}}
                                <div class="font-medium text-gray-700 dark:text-gray-300 text-xs leading-snug break-words">
                                    <span class="text-blue-600 dark:text-blue-400 font-mono text-xs mr-1 font-bold">#{{ $idx + 1 }}</span>
                                    @if($sub)
                                        <a href="{{ url('/submissions/' . $sub->id . '/view') }}" 
                                           target="_blank" 
                                           class="hover:text-primary-600 dark:hover:text-primary-400 hover:underline inline-flex items-center gap-1 group">
                                            <span>{{ $sub->title ?: ($item->item_name ?: 'Publikasi Naskah') }}</span>
                                            <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" class="w-3.5 h-3.5 text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 inline shrink-0" />
                                        </a>
                                    @else
                                        <span>{{ $item->item_name ?: 'Publikasi Naskah' }}</span>
                                    @endif
                                </div>

                                {{-- JOURNAL & AUTHOR --}}
                                <div class="text-[11px] text-gray-400 dark:text-gray-500 flex items-center flex-wrap gap-1">
                                    <span class="font-medium text-gray-600 dark:text-gray-300">{{ $sub?->journal?->name ?? 'Jurnal CIB' }}</span>
                                    <span>·</span>
                                    <span>{{ $sub?->author_name ?: $payerName }}</span>
                                </div>
                            </div>
                            <div class="font-mono font-bold text-gray-900 dark:text-white text-xs sm:text-sm shrink-0 text-right">
                                Rp {{ number_format($item->gross_amount, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                @else
                    @php
                        $sub = $record->submission;
                        $wantsDoi = false;
                        $itemType = null;
                        if ($sub) {
                            $wantsDoi = (bool) ($sub->want_doi || $sub->has_doi);
                            try {
                                $pricing = app(\App\Services\SubmissionPricingService::class)->calculate($sub);
                                $itemType = $pricing['tier_name'] ?? null;
                            } catch (\Throwable $e) {
                                $itemType = null;
                            }
                        }
                        if (empty($itemType)) {
                            if ($record->type === 'doi_addon') {
                                $itemType = 'Add-on DOI Resmi';
                                $wantsDoi = true;
                            } else {
                                $itemType = $record->package_name ?: 'ISSN + DOI (1–5 Author)';
                            }
                        }
                    @endphp
                    <div class="py-1.5 flex justify-between items-start gap-3">
                        <div class="space-y-1 min-w-0 flex-1">
                            {{-- ITEM TYPE & DOI REQUEST BADGE --}}
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-bold text-gray-900 dark:text-white text-xs sm:text-sm">
                                    {{ $itemType }}
                                </span>
                                @if($wantsDoi)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9.5px] font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800">
                                        <x-filament::icon icon="heroicon-m-check-badge" class="w-3 h-3 text-blue-600 dark:text-blue-400" />
                                        Request DOI
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9.5px] font-medium bg-gray-100 text-gray-500 border border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700">
                                        Tanpa DOI
                                    </span>
                                @endif
                            </div>

                            {{-- TITLE --}}
                            <div class="font-medium text-gray-700 dark:text-gray-300 text-xs leading-snug break-words">
                                @if($sub)
                                    <a href="{{ url('/submissions/' . $sub->id . '/view') }}" 
                                       target="_blank" 
                                       class="hover:text-primary-600 dark:hover:text-primary-400 hover:underline inline-flex items-center gap-1 group">
                                        <span>{{ $sub->title ?: ($record->package_name ?: 'Publikasi Naskah') }}</span>
                                        <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" class="w-3.5 h-3.5 text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 inline shrink-0" />
                                    </a>
                                @else
                                    <span>{{ $record->package_name ?: 'Publikasi Naskah' }}</span>
                                @endif
                            </div>

                            {{-- JOURNAL & AUTHOR --}}
                            <div class="text-[11px] text-gray-400 dark:text-gray-500 flex items-center flex-wrap gap-1">
                                <span class="font-medium text-gray-600 dark:text-gray-300">{{ $sub?->journal?->name ?? 'Jurnal CIB' }}</span>
                                <span>·</span>
                                <span>{{ $sub?->author_name ?: $payerName }}</span>
                            </div>
                        </div>
                        <div class="font-mono font-bold text-gray-900 dark:text-white text-xs sm:text-sm shrink-0 text-right">
                            Rp {{ number_format($record->gross_amount, 0, ',', '.') }}
                        </div>
                    </div>
                @endif
            </div>

            <div class="text-[10px] text-gray-400 dark:text-gray-500 flex items-center justify-between px-1">
                <span>Dihitung otomatis oleh sistem settlement</span>
                <span class="font-mono font-semibold text-gray-500">{{ $itemCount }} Naskah</span>
            </div>
        </div>

        {{-- RIGHT COLUMN (5 COLS): INFORMASI PEMBAYAR & PEMBAGIAN HASIL --}}
        <div class="lg:col-span-5 space-y-2.5">
            {{-- INFORMASI PEMBAYAR --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-3 sm:p-3.5 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 block">
                        Informasi Pembayar
                    </span>
                    <span class="font-mono font-bold text-[11px] text-blue-600 dark:text-blue-400">
                        {{ $invoiceNo }}
                    </span>
                </div>

                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 font-bold text-xs flex items-center justify-center shrink-0">
                        {{ $initials }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="font-bold text-gray-900 dark:text-white text-xs truncate">
                            {{ $payerName }}
                        </div>
                        <div class="text-[10.5px] text-gray-400 dark:text-gray-500 truncate">
                            {{ $payerEmail }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- PEMBAGIAN HASIL (SETTLEMENT) --}}
            <div class="bg-[#f4f7fb] dark:bg-gray-800/70 border border-slate-200/80 dark:border-gray-700 rounded-2xl p-3 sm:p-3.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block mb-2">
                    Pembagian Hasil (Settlement)
                </span>

                <div class="space-y-1.5">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-600 dark:text-slate-300">Total Kotor (Gross)</span>
                        <span class="font-mono font-bold text-gray-900 dark:text-white text-xs sm:text-sm">
                            Rp {{ number_format($record->gross_amount, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-600 dark:text-slate-300">MDR QRIS (0,7%)</span>
                        <span class="font-mono font-bold text-amber-600 dark:text-amber-400 text-xs sm:text-sm">
                            - Rp {{ number_format($record->mdr_amount, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-600 dark:text-slate-300">Dev</span>
                        <span class="font-mono font-bold text-blue-500 dark:text-blue-400 text-xs sm:text-sm">
                            - Rp {{ number_format($record->developer_net_share, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="border-t border-slate-200 dark:border-gray-700 my-1 pt-1"></div>

                    <div class="flex justify-between items-center">
                        <span class="font-bold text-gray-900 dark:text-white text-xs sm:text-sm">Admin</span>
                        <span class="font-mono font-black text-blue-600 dark:text-blue-400 text-sm sm:text-base">
                            Rp {{ number_format($record->journal_share, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
