<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pembayaran Kolektif - {{ $payment->invoice_number ?: $payment->order_id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .heading-font {
            font-family: 'Space Grotesk', sans-serif;
        }

        @page {
            size: A4 portrait;
            margin: 0;
        }

        @media print {
            html, body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 210mm !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }

            #invoice-paper {
                box-shadow: none !important;
                border: none !important;
                margin: 0 auto !important;
                width: 210mm !important;
                max-width: 210mm !important;
                min-height: 297mm !important;
                padding: 12mm 14mm !important;
                box-sizing: border-box !important;
                border-radius: 0 !important;
            }
        }
    </style>
</head>

<body class="py-8 px-4 flex flex-col items-center">

    <!-- Action Bar / Buttons -->
    <div
        class="no-print w-full max-w-[210mm] mb-4 flex items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
        <a href="{{ url()->previous() ?: route('filament.admin.resources.submissions.index') }}"
            class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali</span>
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                </svg>
                <span>Download / Cetak</span>
            </button>
        </div>
    </div>

    <!-- Printable Invoice Sheet (A4 Dimensions) -->
    <div id="invoice-paper"
        class="w-full max-w-[210mm] min-h-[280mm] bg-white border border-slate-200/90 rounded-2xl p-8 sm:p-12 shadow-xl text-slate-800 relative overflow-hidden flex flex-col justify-between">

        <!-- Watermark PAID -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.04] select-none">
            <span class="text-[130pt] font-black uppercase tracking-widest text-blue-900 -rotate-45">LUNAS</span>
        </div>

        <div>
            <!-- Header: KOP & Logo -->
            <div class="flex items-center justify-between border-b-2 border-slate-800 pb-6 mb-7">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('assets/logo.png') }}" alt="Logo Cahaya Ilmu Bangsa"
                        class="h-16 w-auto object-contain">
                    <div>
                        <h1 class="text-xl font-black text-slate-900 uppercase tracking-tight">Cahaya Ilmu Bangsa</h1>
                        <p class="text-[10px] text-slate-600 font-semibold tracking-wide uppercase">Kemenkumham
                            AHU-0018912-AH.01.14</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Jl. Raya Sempalwadak No.6, Arjowinangun, Kec.
                            Kedungkandang,</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Kota Malang, Jawa Timur 65132 •
                            admin@cahayailmubangsa.institute</p>
                    </div>
                </div>
                <div class="text-right">
                    <span
                        class="inline-block px-3 py-1 bg-blue-50 border border-blue-300 text-blue-800 text-[11px] font-black uppercase tracking-wider rounded-lg">
                        PAID / LUNAS KOLEKTIF
                    </span>
                    <h2 class="text-2xl font-black text-slate-900 heading-font mt-2">INVOICE</h2>
                    <p class="text-xs font-mono font-bold text-slate-500">
                        {{ $payment->invoice_number ?: ('#INV-BULK-' . $payment->id . '-' . date('ymd', strtotime($payment->paid_at ?? now()))) }}
                    </p>
                </div>
            </div>

            <!-- Meta Information Columns -->
            <div class="flex justify-between items-start gap-6 mb-6 text-xs">
                <div class="max-w-[55%]">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Ditagihkan
                        Kepada:</span>
                    <p class="font-bold text-slate-900 text-sm mb-0.5">
                        {{ $payment->payer_name ?: ($payment->user?->name ?? 'Pemesan / Penulis Kolektif') }}
                    </p>
                    <p class="text-slate-600">{{ $payment->payer_email ?: ($payment->user?->email ?? '-') }}</p>
                    <p class="text-slate-500 mt-1 text-[11px] font-semibold text-blue-700">
                        Total Naskah: {{ count($items) }} Naskah Publikasi
                    </p>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Detail
                        Transaksi Kolektif:</span>
                    <p class="text-slate-700 whitespace-nowrap">
                        <span class="font-semibold text-slate-500">Order ID:</span>
                        <span class="font-mono font-bold text-slate-900">{{ $payment->order_id }}</span>
                    </p>
                    <p class="text-slate-700 whitespace-nowrap">
                        <span class="font-semibold text-slate-500">Tanggal Lunas:</span>
                        <span
                            class="font-medium text-slate-900">{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->translatedFormat('d F Y, H:i') . ' WIB' : now()->translatedFormat('d F Y, H:i') . ' WIB' }}</span>
                    </p>
                    <p class="text-slate-700 whitespace-nowrap">
                        <span class="font-semibold text-slate-500">Metode Bayar:</span>
                        <span class="font-semibold text-slate-900 uppercase">QRIS KOLEKTIF</span>
                    </p>
                </div>
            </div>

            <!-- Table of Items (Bulk List) -->
            <div class="mb-6 overflow-hidden rounded-xl border border-slate-200/90 shadow-sm">
                <table class="w-full text-left text-xs table-fixed">
                    <colgroup>
                        <col style="width: 6%;">
                        <col style="width: 52%;">
                        <col style="width: 24%;">
                        <col style="width: 18%;">
                    </colgroup>
                    <thead
                        class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-3 px-3.5 text-center">No</th>
                            <th class="py-3 px-3.5">Detail Naskah & Penulis</th>
                            <th class="py-3 px-3.5 text-left">Target & Layanan</th>
                            <th class="py-3 px-3.5 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                        @foreach($items as $index => $item)
                            @php
                                $sub = $item->submission;
                                $authors = $sub ? $sub->authors : null;
                                $authorDisplay = '';
                                if ($sub) {
                                    if (is_array($authors) && count($authors) > 0) {
                                        $first = is_array($authors[0]) ? ($authors[0]['name'] ?? '') : $authors[0];
                                        $authorDisplay = count($authors) > 1 ? ($first . ' dkk.') : $first;
                                    } else {
                                        $authorDisplay = $sub->author_name ?: ($sub->user?->name ?? 'Author');
                                    }
                                }
                            @endphp
                            <tr class="align-top hover:bg-slate-50/50 transition-colors">
                                <td class="py-3 px-3.5 text-center font-bold text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-3 px-3.5 pr-4">
                                    <div class="text-[10px] font-mono font-bold text-slate-500 mb-0.5">
                                        ID Naskah: {{ $sub?->id ?? '-' }}
                                    </div>
                                    <h4 class="font-bold text-slate-900 text-xs leading-snug mb-1">
                                        {{ $sub?->title ?: ($item->item_name ?: 'Publikasi Naskah Ilmiah') }}
                                    </h4>
                                    <p class="text-[10.5px] text-slate-500 font-medium">
                                        Penulis: <span
                                            class="text-slate-700 font-semibold">{{ $authorDisplay ?: '-' }}</span>
                                    </p>
                                </td>
                                <td class="py-3 px-3.5 text-left">
                                    <span class="font-bold text-slate-800 block text-xs leading-tight mb-1">
                                        {{ $sub?->journal?->name ?? 'Jurnal CIB' }}
                                    </span>
                                    <div class="flex flex-wrap items-center gap-1">
                                        <span
                                            class="inline-block px-1.5 py-0.5 rounded text-[9.5px] font-semibold bg-slate-100 text-slate-600">
                                            {{ $sub && $sub->isExternal() ? 'Internasional' : 'Nasional ISSN' }}
                                        </span>
                                        @php
                                            $hasDoiInThisBulk = str_contains(strtolower($item->item_name ?? ''), 'doi')
                                                || ($sub && $sub->want_doi && !$sub->payments()->where('type', 'doi_addon')->where('payment_status', 'paid')->exists());
                                        @endphp
                                        @if($hasDoiInThisBulk)
                                            <span
                                                class="inline-block px-1.5 py-0.5 rounded text-[9.5px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                + DOI
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-3.5 text-right font-mono font-bold text-slate-900 text-sm">
                                    Rp {{ number_format($item->gross_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary Total Breakdown -->
            <div class="flex justify-end mb-6">
                <div class="w-80 bg-slate-50/80 p-4 rounded-xl border border-slate-200/80 space-y-2.5 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span class="font-medium">Total Naskah:</span>
                        <span class="font-bold text-slate-800">{{ count($items) }} Artikel</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span class="font-medium">Subtotal Biaya:</span>
                        <span class="font-bold text-slate-800 font-mono">
                            Rp {{ number_format($payment->gross_amount, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="pt-2.5 border-t border-slate-300 flex justify-between items-baseline">
                        <span class="text-xs font-black text-slate-900 uppercase tracking-wider">Total Lunas:</span>
                        <span class="text-2xl font-black text-blue-600 heading-font tracking-tight">
                            Rp {{ number_format($payment->gross_amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer / Signature & Legal Notes -->
        <div class="border-t border-slate-100 pt-5 mt-4 flex items-end justify-between text-xs text-slate-500">
            <div class="max-w-xs space-y-1">
                <p class="font-bold text-slate-800">Catatan Pembayaran Kolektif:</p>
                <p class="text-[10px] text-slate-500 leading-relaxed">
                    Invoice ini merupakan bukti sah pembayaran kolektif biaya publikasi artikel & penerbitan Letter of
                    Acceptance (LOA) di Cahaya Ilmu Bangsa Institute yang diterbitkan secara elektronik oleh sistem.
                </p>
            </div>
            <div class="text-center">
                <div
                    class="inline-block p-2 border-2 border-blue-600 rounded-xl text-blue-700 font-black text-xs uppercase tracking-widest mb-1 rotate-[-3deg]">
                    LUNAS TERVERIFIKASI
                </div>
                <p class="text-[10px] text-slate-400 font-mono">Verifikasi Sistem CIB Institute</p>
            </div>
        </div>

    </div>
</body>

</html>