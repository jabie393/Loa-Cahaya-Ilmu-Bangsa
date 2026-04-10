<html>

<head>
    <link rel="preconnect"
          href="https://fonts.googleapis.com" />
    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&display=swap"
          rel="stylesheet" />
    <meta content="text/html; charset=UTF-8"
          http-equiv="content-type" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-primary: #1b4332;
            --color-secondary: #2d6a4f;
            --color-background: #faf9f6;

            --font-newsreader: "Newsreader", "serif";
            --font-merriweather: "Merriweather", "serif";
        }

        @page {
            size: A4;
            margin: 0;
        }

        @media print {
            body {
                background-color: transparent;
            }

            .print-a4 {
                margin: 0px !important;
                box-shadow: none !important;
                min-height: 0 !important;
                height: auto !important;
                width: 100% !important;
                overflow: hidden !important;
            }
        }
    </style>
</head>
<button onclick="window.print()" class="fixed bottom-8 right-8 z-50 bg-primary text-white px-6 py-3 rounded-xl shadow-2xl hover:scale-105 transition-transform active:scale-95 print:hidden font-bold flex items-center gap-2">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
    </svg>
    Download PDF
</button>
<body class="font-newsreader print-a4 bg-background mx-auto my-[10mm] box-border max-h-[297mm] w-[210mm] px-[72pt] pb-[72pt] pt-[20pt] text-[10pt] text-black shadow-[0_0_10px_rgba(0,0,0,0.2)]">
    <div class="text-center">
        <p class="text-primary font-newsreader mb-[-2pt] text-[20pt] font-bold">
            CAHAYA ILMU BANGSA INSTITUTE
        </p>
        <p class="font-newsreader text-[10pt] font-bold text-gray-500">
            Tashdiq: Jurnal Kajian Agama dan Dakwah
        </p>
    </div>
    <div class="top-20 flex justify-between">
        <div class="align-center flex w-full justify-between">
            <div class="flex flex-col justify-center pt-5">
                <p class="font-newsreader text-primary text-[10pt] font-bold">
                    Biro Penelitian, Publikasi, dan Pengabdian Kepada
                    Masyarakat
                </p>
                <p class="font-newsreader text-[7pt]">
                    KEMENKUMHAM AHU-0018912-AH.01.14
                </p>
                <p class="font-newsreader text-[7pt]">
                    Perum Puri Kartika Asri Blok 2 A2 Malang
                </p>
                <p class="font-newsreader text-[7pt]">
                    e-mail:
                    <a href="mailto:admin@cahayailmubangsa.institute">
                        admin@cahayailmubangsa.institute
                    </a>
                </p>
            </div>
            <div class="flex items-end">
                <img src="https://cibangsa.com/public/journals/14/journalThumbnail_en.jpg"
                     alt=""
                     class="w-[70px]" />
            </div>
        </div>
    </div>
    <div class="bg-primary/20 my-8 h-[1px] w-full"></div>

    <div class="text-center">
        <h1 class="text-primary font-newsreader text-[25pt] font-[1000] leading-none">
            LETTER OF ACCEPTANCE
        </h1>
        <div class="bg-primary mx-auto my-1 h-[2px] w-[200px]"></div>
        <p class="font-newsreader mt-2 text-[12pt] tracking-[0.3em] text-gray-800">
            NO: {{ $record->created_at->format('Y') }}/CIB{{ sprintf('%03d', $record->journal->id) }}/LOA{{ sprintf('%03d', $record->id) }}
        </p>
    </div>

    <div class="py-5">
        <p class="text-secondary font-bold italic">
            Assalamualaikum Wr. Wb.
        </p>
        <p class="font-newsreader text-[10pt] text-gray-700">
            Bersama surat ini, kami menerangkan bahwa artikel dengan
            keterangan naskah berikut
        </p>
    </div>

    <!-- Content -->
    <table class="w-full border-collapse text-[10pt]">
        <tr class="h-[55pt]">
            <td class="font-newsreader w-[91.9pt] px-[5.4pt] py-4 align-top font-semibold uppercase text-gray-500">
                Judul
            </td>
            <td class="font-newsreader px-[5.4pt] py-4 align-top">
                {{ $record->title }}
            </td>
        </tr>
        <tr class="h-[28.1pt]">
            <td class="font-newsreader w-[91.9pt] px-[5.4pt] py-2 align-top font-semibold uppercase text-gray-500">
                Author
            </td>
            <td class="font-newsreader px-[5.4pt] py-2 align-top">
                {{ $record->author_name }}
            </td>
        </tr>
        <tr class="h-[56pt]">
            <td class="font-newsreader w-[91.9pt] px-[5.4pt] py-4 align-top font-semibold uppercase text-gray-500">
                Instansi
            </td>
            <td class="font-newsreader px-[5.4pt] py-4 text-justify align-top">
                {{ $record->institution }}
            </td>
        </tr>
        <tr class="h-[29.4pt]">
            <td class="font-newsreader w-[91.9pt] px-[5.4pt] py-2 align-top font-semibold uppercase text-gray-500">
                Korespondensi
            </td>
            <td class="font-newsreader px-[5.4pt] py-2 align-top">
                <a href="mailto:{{ $record->email }}"
                   class="hover:text-primary transition-colors">
                    {{ $record->email }}
                </a>
            </td>
        </tr>
        <tr class="h-[27.4pt]">
            <td class="font-newsreader w-[91.9pt] px-[5.4pt] py-2 align-top font-semibold uppercase text-gray-500">
                Jurnal
            </td>
            <td class="font-newsreader px-[5.4pt] py-2 align-top">
                {{ $record->journal->name }}
            </td>
        </tr>
        <tr class="h-[27.2pt]">
            <td class="font-newsreader w-[91.9pt] px-[5.4pt] py-2 align-top font-semibold uppercase text-gray-500">
                Volume
            </td>
            <td class="font-newsreader px-[5.4pt] py-2 align-top">
                {{ $record->volume }}
            </td>
        </tr>
        <tr class="h-[37.5pt]">
            <td class="font-newsreader w-[91.9pt] px-[5.4pt] py-2 align-top font-semibold uppercase text-gray-500">
                Link Terbitan
            </td>
            <td class="font-newsreader px-[5.4pt] py-2 align-top">
                <a href="{{ $record->publication_link }}"
                   class="hover:text-primary transition-colors">
                    {{ $record->publication_link }}
                </a>
            </td>
        </tr>
    </table>
    <div class="pb-5">
        <p class="font-newsreader text-justify text-[10pt] font-normal leading-relaxed text-black/80">
            Berstatus ACCEPTED untuk dipublish. Keputusan ini dibuat sebagai
            tanda bahwa naskah yang bersangkutan telah lolos plagiarism
            checker. Dan LoA ini dibuat sebagai bukti bahwa author telah
            menyelesaikan APC yang telah ditetapkan oleh pengelola jurnal.
            LOA Berlaku jika dilengkapi link dan pdf publish. Hubungi kami
            di
            <a href="mailto:admin_jurnal@cahayailmubangsa.institute"
               class="text-primary font-semibold">
                admin_jurnal@cahayailmubangsa.institute
            </a>
            jika ada pertanyaan lebih lanjut, terima kasih.
        </p>
    </div>
    <div class="ml-auto w-fit text-left">
        <p class="font-newsreader mb-4 text-[10pt] font-normal text-black">
            Malang, {{ $record->approved_date->format('d F Y') }}
        </p>
        <div class="mb-4">
            <img alt="Signature"
                 src="{{ asset('assets/qrcode.png') }}"
                 class="h-[73.33px] w-[73.33px] object-contain" />
        </div>
        <p class="font-newsreader text-[11pt] font-bold leading-tight text-black">
            Dr. Khoirul Umam, M.Pd.I
        </p>
        <p class="font-newsreader font-bold text-black">Director</p>
    </div>
</body>

</html>
