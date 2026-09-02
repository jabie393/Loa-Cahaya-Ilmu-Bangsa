<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pembayaran - Submission #{{ $submission->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap"
        rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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

        @media print {
            body {
                background: white;
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            #invoice-paper {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                width: 100% !important;
                padding: 15mm 20mm !important;
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
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                </svg>
                <span>Cetak</span>
            </button>
            <button id="btnDownloadPdf" onclick="downloadInvoicePDF()"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Download PDF</span>
            </button>
        </div>
    </div>

    <!-- Printable Invoice Sheet (A4 Dimensions) -->
    <div id="invoice-paper"
        class="w-full max-w-[210mm] min-h-[280mm] bg-white border border-slate-200/90 rounded-2xl p-10 sm:p-14 shadow-xl text-slate-800 relative overflow-hidden flex flex-col justify-between">

        <!-- Watermark PAID -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.04] select-none">
            <span class="text-[130pt] font-black uppercase tracking-widest text-emerald-900 -rotate-45">LUNAS</span>
        </div>

        <div>
            <!-- Header: KOP & Logo -->
            <div class="flex items-center justify-between border-b-2 border-slate-800 pb-6 mb-8">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('assets/logo.png') }}" alt="Logo Cahaya Ilmu Bangsa"
                        class="h-16 w-auto object-contain">
                    <div>
                        <h1 class="text-xl font-black text-slate-900 uppercase tracking-tight">Cahaya Ilmu Bangsa</h1>
                        <p class="text-[10px] text-slate-600 font-semibold tracking-wide uppercase">Kemenkumham
                            AHU-0018912-AH.01.14</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Jl. Raya Sempalwadak No.6, Arjowinangun, Kec.
                            Kedungkandang,
                        </p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Kota Malang, Jawa Timur 65132
                            <br>• admin@cahayailmubangsa.institute
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span
                        class="inline-block px-3 py-1 bg-blue-50 border border-blue-300 text-blue-800 text-[11px] font-black uppercase tracking-wider rounded-lg">
                        PAID / LUNAS
                    </span>
                    <h2 class="text-2xl font-black text-slate-900 heading-font mt-2">INVOICE</h2>
                    <p class="text-xs font-mono font-bold text-slate-500">
                        #INV-{{ $submission->id }}-{{ date('ymd', strtotime($latestPaidAt ?? now())) }}
                    </p>
                </div>
            </div>

            <!-- Meta Information Columns -->
            <div class="grid grid-cols-2 gap-8 mb-8 text-xs">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Ditagihkan
                        Kepada:</span>
                    @php
                        $firstAuthorName = null;
                        $hasMultipleAuthors = false;

                        if (!empty($submission->authors) && is_array($submission->authors) && count($submission->authors) > 0) {
                            $first = $submission->authors[0];
                            $firstAuthorName = is_array($first) ? ($first['name'] ?? null) : $first;
                            if (count($submission->authors) > 1) {
                                $hasMultipleAuthors = true;
                            }
                        }

                        if (empty($firstAuthorName)) {
                            $firstAuthorName = $submission->author_name ?: ($submission->user?->name ?? 'Author');
                        }

                        $displayName = $hasMultipleAuthors ? ($firstAuthorName . ' dkk.') : $firstAuthorName;
                    @endphp
                    <p class="font-bold text-slate-900 text-sm mb-0.5">
                        {{ $displayName }}
                    </p>
                    <p class="text-slate-600">{{ $submission->email }}</p>
                    @if(!empty($submission->authors) && is_array($submission->authors))
                        <p class="text-slate-500 mt-1 text-[11px]">Jumlah Penulis: {{ count($submission->authors) }} Orang
                        </p>
                    @endif
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Detail
                        Transaksi:</span>
                    <p class="text-slate-700">
                        <span class="font-semibold text-slate-500">No. Registrasi:</span>
                        <span class="font-mono font-bold text-slate-900">{{ $submission->id }}</span>
                    </p>
                    <p class="text-slate-700">
                        <span class="font-semibold text-slate-500">Tanggal Terbit:</span>
                        <span
                            class="font-medium text-slate-900">{{ now()->translatedFormat('d F Y, H:i') . ' WIB' }}</span>
                    </p>
                    <p class="text-slate-700">
                        <span class="font-semibold text-slate-500">Metode Bayar:</span>
                        <span class="font-semibold text-slate-900 uppercase">QRIS Midtrans Dinamis</span>
                    </p>
                </div>
            </div>

            <!-- Table of Items (Cumulative: Publication + DOI Add-on) -->
            <div class="mb-8 overflow-hidden rounded-xl border border-slate-200/90 shadow-sm">
                <table class="w-full text-left text-xs table-fixed">
                    <colgroup>
                        <col style="width: 7%;">
                        <col style="width: 53%;">
                        <col style="width: 22%;">
                        <col style="width: 18%;">
                    </colgroup>
                    <thead
                        class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4 text-center">No</th>
                            <th class="py-3.5 px-4">Deskripsi Layanan</th>
                            <th class="py-3.5 px-4 text-left">Target / Kategori</th>
                            <th class="py-3.5 px-4 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                        @php $itemNo = 1; @endphp

                        {{-- Line 1: Publication Fee --}}
                        @if($submissionPayment)
                            <tr class="align-top hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-4 text-center font-bold text-slate-400">{{ $itemNo++ }}</td>
                                <td class="py-4 px-4 pr-6">
                                    <div
                                        class="text-[10.5px] font-mono text-slate-500 font-bold mb-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                        <div class="flex items-center gap-1">
                                            <span
                                                class="text-slate-400 font-sans font-semibold text-[10px] uppercase tracking-wider">Order
                                                ID:</span>
                                            <span>{{ $submissionPayment->order_id }}</span>
                                        </div>
                                        @if($submissionPayment->paid_at)
                                            <span class="text-slate-300">•</span>
                                            <div class="flex items-center gap-1 text-slate-400 font-sans text-[10.5px]">
                                                <span>Lunas:</span>
                                                <span
                                                    class="font-semibold text-slate-600">{{ \Carbon\Carbon::parse($submissionPayment->paid_at)->translatedFormat('d M Y, H:i') }}
                                                    WIB</span>
                                            </div>
                                        @endif
                                    </div>
                                    <h4 class="font-bold text-slate-900 text-xs leading-relaxed mb-2 uppercase">
                                        {{ !empty($submission->title) ? $submission->title : 'Biaya Publikasi Naskah Ilmiah' }}
                                    </h4>
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 bg-slate-100 text-slate-700 text-[10px] font-semibold rounded">
                                            {{ $pricing['tier_name'] ?? 'Publikasi Standar' }}
                                        </span>
                                        @if($submission->want_doi && !$doiPayment)
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[10px] font-semibold rounded border border-emerald-200">
                                                Termasuk DOI
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-left">
                                    <span class="font-bold text-slate-800 block text-xs leading-tight mb-0.5">
                                        {{ $submission->journal?->name ?? 'Jurnal CIB' }}
                                    </span>
                                    <span class="inline-block text-[10px] text-slate-500 font-medium">
                                        {{ $submission->isExternal() ? 'Jurnal Internasional' : 'Jurnal Nasional ISSN' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <span class="font-bold text-slate-900 text-sm font-mono block">
                                        Rp {{ number_format($submissionPayment->gross_amount, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        @endif

                        {{-- Line 2: Add-on DOI (If author bought it subsequently) --}}
                        @if($doiPayment)
                            <tr class="align-top hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-4 text-center font-bold text-slate-400">{{ $itemNo++ }}</td>
                                <td class="py-4 px-4 pr-6">
                                    <div
                                        class="text-[10.5px] font-mono text-slate-500 font-bold mb-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                        <div class="flex items-center gap-1">
                                            <span
                                                class="text-slate-400 font-sans font-semibold text-[10px] uppercase tracking-wider">Order
                                                ID:</span>
                                            <span>{{ $doiPayment->order_id }}</span>
                                        </div>
                                        @if($doiPayment->paid_at)
                                            <span class="text-slate-300">•</span>
                                            <div class="flex items-center gap-1 text-slate-400 font-sans text-[10.5px]">
                                                <span>Lunas:</span>
                                                <span
                                                    class="font-semibold text-slate-600">{{ \Carbon\Carbon::parse($doiPayment->paid_at)->translatedFormat('d M Y, H:i') }}
                                                    WIB</span>
                                            </div>
                                        @endif
                                    </div>
                                    <h4 class="font-bold text-slate-900 text-xs leading-relaxed mb-2 uppercase">
                                        Add-on Repository Identifier (DOI Resmi)
                                    </h4>
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded border border-emerald-200">
                                            {{ $submission->repository_identifier ?: 'DOI Terverifikasi' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-left">
                                    <span class="font-bold text-slate-800 block text-xs leading-tight mb-0.5">
                                        Repository CIB
                                    </span>
                                    <span class="inline-block text-[10px] text-emerald-600 font-medium">
                                        Permanent DOI Link
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <span class="font-bold text-slate-900 text-sm font-mono block">
                                        Rp {{ number_format($doiPayment->gross_amount, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Summary Total Breakdown -->
            <div class="flex justify-end mb-8">
                <div class="w-80 bg-slate-50/70 p-4 rounded-xl border border-slate-200/80 space-y-2.5 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span class="font-medium">Subtotal Biaya:</span>
                        <span class="font-bold text-slate-800 font-mono">Rp
                            {{ number_format($totalPaid, 0, ',', '.') }}</span>
                    </div>
                    <div class="pt-2.5 border-t border-slate-300 flex justify-between items-baseline">
                        <span class="text-xs font-black text-slate-900 uppercase tracking-wider">Total Lunas:</span>
                        <span class="text-2xl font-black text-blue-600 heading-font tracking-tight">
                            Rp {{ number_format($totalPaid, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer / Signature & Legal Notes -->
        <div class="border-t border-slate-100 pt-6 mt-6 flex items-end justify-between text-xs text-slate-500">
            <div class="max-w-xs space-y-1">
                <p class="font-bold text-slate-800">Catatan Pembayaran:</p>
                <p class="text-[10px] text-slate-500 leading-relaxed">
                    Invoice ini merupakan bukti sah pembayaran biaya publikasi artikel & penerbitan Letter of Acceptance
                    (LOA) di Cahaya Ilmu Bangsa Institute yang diterbitkan secara elektronik oleh sistem.
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

    <!-- PDF Generation Script -->
    <script>
        async function downloadInvoicePDF() {
            const btn = document.getElementById("btnDownloadPdf");
            if (btn) {
                btn.style.opacity = "0.7";
                btn.innerText = "Membuat PDF...";
            }

            const element = document.getElementById("invoice-paper");
            const opt = {
                scale: 2,
                useCORS: true,
                logging: false,
                backgroundColor: "#ffffff"
            };

            try {
                const canvas = await html2canvas(element, opt);
                const imgData = canvas.toDataURL("image/jpeg", 0.95);
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF("p", "mm", "a4");

                const pageWidth = 210;
                const pageHeight = 297;
                const imgWidth = pageWidth;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;

                pdf.addImage(imgData, "JPEG", 0, 0, imgWidth, Math.min(imgHeight, pageHeight));
                pdf.save("Invoice-{{ $submission->id }}-" + "{{ date('Ymd') }}.pdf");
            } catch (e) {
                console.error("PDF generation failed, fallback to print:", e);
                window.print();
            } finally {
                if (btn) {
                    btn.style.opacity = "1";
                    btn.innerHTML = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg><span>Download PDF</span>';
                }
            }
        }
    </script>
</body>

</html>