<html>

<head>
    <link rel="preconnect"
          href="https://fonts.googleapis.com" />
    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap"
          rel="stylesheet" />
    <meta content="text/html; charset=UTF-8"
          http-equiv="content-type" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "var(--color-primary)",
                        secondary: "var(--color-secondary)",
                        background: "var(--color-background)",
                    },
                    fontFamily: {
                        space: ["Space Grotesk", "sans-serif"],
                    },
                },
            },
        };
    </script>
    <style type="text/tailwindcss">
        /* Base layer fully removed; cascading utilities set on body instead */
        @page {
            size: A4;
            margin: 0;
            max-height: A4;
        }

        @media print {
            body {
                @apply bg-[#FAF9F6];
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

        :root {
            --color-primary: #008769;
            --color-secondary: #1a1c1e;
            --color-background: white;
        }
    </style>
</head>
<button onclick="window.print()" class="fixed bottom-8 right-8 z-50 bg-primary text-white px-6 py-3 rounded-xl shadow-2xl hover:scale-105 transition-transform active:scale-95 print:hidden font-bold flex items-center gap-2">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
    </svg>
    Download PDF
</button>
<body class="font-space print-a4 bg-background mx-auto my-[10mm] box-border max-h-[297mm] w-[210mm] px-[72pt] pb-[72pt] pt-[20pt] text-[10pt] text-black shadow-[0_0_10px_rgba(0,0,0,0.2)]">
    <div class="top-20 flex justify-between">
        <div class="">
            <div class="bg-primary h-[10px] w-[100px]"></div>
            <p class="text-primary font-space mb-[-15px] text-[40pt] font-[1000] uppercase">
                Musytari
            </p>
            <p class="text-secondary font-space text-[10pt] uppercase">
                Jurnal Manajemen, Akuntansi, dan Ekonomi
            </p>
            <div class="flex flex-col justify-between pt-5">
                <div>
                    <div class="">
                        <p class="text-primary font-space mb-[2pt] text-[10pt] font-[1000]">
                            CAHAYA ILMU BANGSA INSTITUTE
                        </p>
                        <p class="font-space text-[7pt]">
                            KEMENKUMHAM AHU-0018912-AH.01.14
                        </p>
                        <p class="font-space text-[7pt]">
                            Perum Puri Kartika Asri Blok 2 A2 Malang
                        </p>
                        <p class="font-space text-[7pt]">
                            e-mail:
                            <a href="mailto:admin@cahayailmubangsa.institute">
                                admin@cahayailmubangsa.institute
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <img src="https://cibangsa.com/public/journals/1/journalThumbnail_en.jpg"
                 alt=""
                 class="w-[120px]" />
        </div>
    </div>
    <div class="bg-primary my-5 h-[1px] w-full"></div>

    <div class="">
        <h1 class="text-primary font-space text-[30pt] font-[1000] leading-none">
            LETTER OF ACCEPTANCE
        </h1>
        <p class="font-space mt-2 text-[12pt] font-bold text-black">
            NO: {{ $record->created_at->format('Y') }}/CIB{{ sprintf('%03d', $record->journal->id) }}/LOA{{ sprintf('%03d', $record->id) }}
        </p>
    </div>

    <div class="py-5">
        <p class="text-left italic leading-[1.079]">
            <span class="font-space text-[10pt] font-bold text-black">
                Assalamualaikum Wr. Wb.
            </span>
        </p>
        <p class="text-left leading-[1.079]">
            <span class="font-space text-[10pt] text-gray-700">
                Bersama surat ini, kami menerangkan bahwa artikel dengan
                keterangan naskah berikut
            </span>
        </p>
    </div>

    <!-- Content -->
    <table class="w-full border-collapse text-[10pt]">
        <tr class="h-[55pt] border border-black">
            <td class="font-space w-[91.9pt] px-[5.4pt] py-4 align-top font-semibold text-black">
                Judul
            </td>
            <td class="font-space border border-black bg-gray-200 px-[5.4pt] py-4 align-top">
                {{ $record->title }}
            </td>
        </tr>
        <tr class="h-[28.1pt] border border-black">
            <td class="font-space w-[91.9pt] px-[5.4pt] py-2 align-top font-semibold text-black">
                Author
            </td>
            <td class="font-space border border-black bg-gray-200 px-[5.4pt] py-2 align-top">
                {{ $record->author_name }}
            </td>
        </tr>
        <tr class="h-[56pt] border border-black">
            <td class="font-space w-[91.9pt] px-[5.4pt] py-4 align-top font-semibold text-black">
                Instansi
            </td>
            <td class="font-space border border-black bg-gray-200 px-[5.4pt] py-4 text-justify align-top">
                {{ $record->institution }}
            </td>
        </tr>
        <tr class="h-[29.4pt] border border-black">
            <td class="font-space w-[91.9pt] px-[5.4pt] py-2 align-top font-semibold text-black">
                Korespondensi
            </td>
            <td class="font-space border border-black bg-gray-200 px-[5.4pt] py-2 align-top">
                <a href="mailto:{{ $record->email }}"
                   class="hover:text-primary transition-colors">
                    {{ $record->email }}
                </a>
            </td>
        </tr>
        <tr class="h-[27.4pt] border border-black">
            <td class="font-space w-[91.9pt] px-[5.4pt] py-2 align-top font-semibold text-black">
                Jurnal
            </td>
            <td class="font-space border border-black bg-gray-200 px-[5.4pt] py-2 align-top">
                {{ $record->journal->name }}
            </td>
        </tr>
        <tr class="h-[27.2pt] border border-black">
            <td class="font-space w-[91.9pt] px-[5.4pt] py-2 align-top font-semibold text-black">
                Volume
            </td>
            <td class="font-space border border-black bg-gray-200 px-[5.4pt] py-2 align-top">
                {{ $record->volume }}
            </td>
        </tr>
        <tr class="h-[37.5pt] border border-black">
            <td class="font-space w-[91.9pt] px-[5.4pt] py-2 align-top font-semibold text-black">
                Link Terbitan
            </td>
            <td class="font-space border border-black bg-gray-200 px-[5.4pt] py-2 align-top">
                <a href="{{ $record->publication_link }}"
                   class="hover:text-primary transition-colors">
                    {{ $record->publication_link }}
                </a>
            </td>
        </tr>
    </table>
    <div class="py-5">
        <p class="font-space text-justify text-[10pt] font-normal leading-relaxed text-black/80">
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
    <div class="ml-auto mt-5 w-fit text-left">
        <p class="font-space mb-4 text-[10pt] font-normal text-black">
            Malang, {{ $record->approved_date->format('d F Y') }}
        </p>
        <div class="mb-4">
            <img alt="Signature"
                 src="{{ asset('assets/qrcode.png') }}"
                 class="h-[73.33px] w-[73.33px] object-contain" />
        </div>
        <p class="font-space text-[11pt] font-bold leading-tight text-black">
            Dr. Fahad Ismail, M.Pd., PhD
        </p>
        <p class="font-space font-bold text-black">Director</p>
    </div>
</body>

</html>
