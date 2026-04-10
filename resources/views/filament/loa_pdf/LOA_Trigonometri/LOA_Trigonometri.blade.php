<html>

<head>
    <link rel="preconnect"
          href="https://fonts.googleapis.com" />
    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
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
                        inter: ["Inter", "sans-serif"],
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
            --color-primary: #006e2f;
            --color-secondary: #00b050;
            --color-background: white;
        }
    </style>
</head>

<body class="font-inter print-a4 bg-background mx-auto my-[10mm] box-border max-h-[297mm] w-[210mm] px-[72pt] pb-[72pt] pt-[20pt] text-[10pt] text-black shadow-[0_0_10px_rgba(0,0,0,0.2)]">
    <div class="bg-primary relative left-[-96px] top-[-27px] z-[-10] h-[15px] w-[210mm]"></div>
    <div class="top-20 flex justify-between">
        <div class="text-center">
            <p class="text-primary font-inter m-[-4pt] text-[20pt] font-[1000] italic">
                TRIGONOMETRI
            </p>
            <p class="font-inter text-[6.7pt] font-bold text-black">
                Jurnal Matematika dan Ilmu Pengetahuan Alam
            </p>
        </div>
        <div class="flex flex-col justify-between">
            <div>
                <div class="text-right">
                    <p class="font-inter mb-[2pt] text-[10pt] font-[1000] text-black">
                        CAHAYA ILMU BANGSA INSTITUTE
                    </p>
                    <p class="font-inter text-[7pt]">
                        KEMENKUMHAM AHU-0018912-AH.01.14
                    </p>
                    <p class="font-inter text-[7pt]">
                        Perum Puri Kartika Asri Blok 2 A2 Malang
                    </p>
                    <p class="font-inter text-[7pt]">
                        e-mail:
                        <a href="mailto:admin@cahayailmubangsa.institute">
                            admin@cahayailmubangsa.institute
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="pt-5 text-center">
        <h1 class="text-primary font-inter text-[30pt] font-[1000] leading-none">
            LETTER OF ACCEPTANCE
        </h1>
        <div class="font-inter mt-2 flex items-center justify-center gap-3 text-[12pt] font-bold text-black">
            <span class="bg-primary/30 h-[1px] w-8"></span>
            NO: 2025/CIB/LOA
            <span class="bg-primary/30 h-[1px] w-8"></span>
        </div>
    </div>

    <div class="bg-primary my-5 h-[8px] w-full"></div>

    <div class="py-5">
        <p class="text-left italic leading-[1.079]">
            <span class="font-inter text-[10pt] font-bold text-black">
                Assalamualaikum Wr. Wb.
            </span>
        </p>
        <p class="text-left leading-[1.079]">
            <span class="font-inter text-[10pt] text-gray-700">
                Bersama surat ini, kami menerangkan bahwa artikel dengan
                keterangan naskah berikut
            </span>
        </p>
    </div>

    <!-- Content -->
    <table class="w-full border-collapse text-[10pt]">
        <tr class="h-[55pt] border border-black">
            <td class="font-inter w-[91.9pt] bg-gray-200 px-[5.4pt] py-4 align-top font-semibold text-black">
                Judul
            </td>
            <td class="font-inter border border-black px-[5.4pt] py-4 align-top">
                {{ $record->title }}
            </td>
        </tr>
        <tr class="h-[28.1pt] border border-black">
            <td class="font-inter w-[91.9pt] bg-gray-200 px-[5.4pt] py-2 align-top font-semibold text-black">
                Author
            </td>
            <td class="font-inter border border-black px-[5.4pt] py-2 align-top">
                {{ $record->author_name }}
            </td>
        </tr>
        <tr class="h-[56pt] border border-black">
            <td class="font-inter w-[91.9pt] bg-gray-200 px-[5.4pt] py-4 align-top font-semibold text-black">
                Instansi
            </td>
            <td class="font-inter border border-black px-[5.4pt] py-4 text-justify align-top">
                {{ $record->institution }}
            </td>
        </tr>
        <tr class="h-[29.4pt] border border-black">
            <td class="font-inter w-[91.9pt] bg-gray-200 px-[5.4pt] py-2 align-top font-semibold text-black">
                Korespondensi
            </td>
            <td class="font-inter border border-black px-[5.4pt] py-2 align-top">
                <a href="mailto:{{ $record->email }}"
                   class="hover:text-primary transition-colors">
                    {{ $record->email }}
                </a>
            </td>
        </tr>
        <tr class="h-[27.4pt] border border-black">
            <td class="font-inter w-[91.9pt] bg-gray-200 px-[5.4pt] py-2 align-top font-semibold text-black">
                Jurnal
            </td>
            <td class="font-inter border border-black px-[5.4pt] py-2 align-top">
                {{ $record->journal->name }}
            </td>
        </tr>
        <tr class="h-[27.2pt] border border-black">
            <td class="font-inter w-[91.9pt] bg-gray-200 px-[5.4pt] py-2 align-top font-semibold text-black">
                Volume
            </td>
            <td class="font-inter border border-black px-[5.4pt] py-2 align-top">
                {{ $record->volume }}
            </td>
        </tr>
        <tr class="h-[37.5pt] border border-black">
            <td class="font-inter w-[91.9pt] bg-gray-200 px-[5.4pt] py-2 align-top font-semibold text-black">
                Link Terbitan
            </td>
            <td class="font-inter border border-black px-[5.4pt] py-2 align-top">
                <a href="{{ $record->publication_link }}"
                   class="hover:text-primary transition-colors">
                    {{ $record->publication_link }}
                </a>
            </td>
        </tr>
    </table>
    <div class="py-6">
        <p class="font-inter text-justify text-[10pt] font-normal italic leading-relaxed text-black/80">
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
    <div class="ml-auto mt-8 w-fit text-left">
        <p class="font-inter mb-4 text-[10pt] font-normal text-black">
            Malang, {{ $record->approved_date->format('d F Y') }}
        </p>
        <div class="mb-4">
            <img alt="Signature"
                 src="{{ asset('assets/qrcode.png') }}"
                 class="h-[73.33px] w-[73.33px] object-contain" />
        </div>
        <p class="font-inter text-[11pt] font-bold leading-tight text-black">
            Dr. Fahad Ismail, M.Pd., PhD
        </p>
        <p class="font-inter font-bold text-black">Director</p>
    </div>
</body>

</html>
