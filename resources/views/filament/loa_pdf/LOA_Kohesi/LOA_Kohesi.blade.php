<html>

<head>
    <link rel="preconnect"
          href="https://fonts.googleapis.com" />
    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap"
          rel="stylesheet" />

    <meta content="text/html; charset=UTF-8"
          http-equiv="content-type" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-primary: #0a192f;
            --color-secondary: #c5a059;
            --color-background: #faf9f6;

            --font-manrope: "Manrope", "sans-serif";
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

<body class="font-manrope print-a4 bg-background mx-auto my-[10mm] box-border max-h-[297mm] w-[210mm] px-[72pt] pb-[72pt] pt-[20pt] text-[10pt] text-black shadow-[0_0_10px_rgba(0,0,0,0.2)]">
    <div class="top-20 flex justify-between">
        <div class="">
            <p class="text-secondary font-manrope mb-[-15px] text-[40pt] font-[1000] uppercase">
                Kohesi
            </p>
            <p class="text-primary font-manrope text-[10pt] uppercase">
                Jurnal Sains dan Teknologi
            </p>
            <div class="bg-secondary mt-5 h-[5px] w-[100px]"></div>
            <div class="flex flex-col justify-between pt-5">
                <div>
                    <div class="">
                        <p class="text-primary font-manrope mb-[2pt] text-[10pt] font-[1000]">
                            CAHAYA ILMU BANGSA INSTITUTE
                        </p>
                        <p class="font-manrope text-[7pt]">
                            Biro Penelitian, Publikasi, dan Pengabdian
                            Kepada Masyarakat
                        </p>
                        <p class="font-manrope text-[7pt]">
                            KEMENKUMHAM AHU-0018912-AH.01.14
                        </p>
                        <p class="font-manrope text-[7pt]">
                            Perum Puri Kartika Asri Blok 2 A2 Malang
                        </p>
                        <p class="font-manrope text-[7pt]">
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
            <img src="https://cibangsa.com/public/journals/13/journalThumbnail_en.jpg"
                 alt=""
                 class="w-[120px]" />
        </div>
    </div>
    <div class="bg-secondary my-5 h-[5px] w-full"></div>

    <div class="text-center">
        <h1 class="text-primary font-manrope text-[30pt] font-[1000] leading-none">
            LETTER OF ACCEPTANCE
        </h1>
        <p class="font-manrope mt-2 text-[12pt] font-bold text-black">
            NO: 2025/CIB/LOA
        </p>
    </div>

    <div class="py-5">
        <p class="text-left italic leading-[1.079]">
            <span class="font-manrope text-[10pt] font-bold text-black">
                Assalamualaikum Wr. Wb.
            </span>
        </p>
        <p class="text-left leading-[1.079]">
            <span class="font-manrope text-[10pt] text-gray-700">
                Bersama surat ini, kami menerangkan bahwa artikel dengan
                keterangan naskah berikut
            </span>
        </p>
    </div>

    <!-- Content -->
    <table class="w-full border-collapse text-[10pt]">
        <tr class="h-[55pt] border border-black">
            <td class="font-manrope w-[91.9pt] bg-gray-300 px-[5.4pt] py-4 align-top font-semibold text-black">
                Judul
            </td>
            <td class="font-manrope border border-black bg-gray-200 px-[5.4pt] py-4 align-top">
                {{ $record->title }}
            </td>
        </tr>
        <tr class="h-[28.1pt] border border-black">
            <td class="font-manrope w-[91.9pt] bg-gray-300 px-[5.4pt] py-2 align-top font-semibold text-black">
                Author
            </td>
            <td class="font-manrope border border-black bg-gray-200 px-[5.4pt] py-2 align-top">
                {{ $record->author_name }}
            </td>
        </tr>
        <tr class="h-[56pt] border border-black">
            <td class="font-manrope w-[91.9pt] bg-gray-300 px-[5.4pt] py-4 align-top font-semibold text-black">
                Instansi
            </td>
            <td class="font-manrope border border-black bg-gray-200 px-[5.4pt] py-4 text-justify align-top">
                {{ $record->institution }}
            </td>
        </tr>
        <tr class="h-[29.4pt] border border-black">
            <td class="font-manrope w-[91.9pt] bg-gray-300 px-[5.4pt] py-2 align-top font-semibold text-black">
                Korespondensi
            </td>
            <td class="font-manrope border border-black bg-gray-200 px-[5.4pt] py-2 align-top">
                <a href="mailto:aaaaaa@gmail.com"
                   class="hover:text-primary transition-colors">
                    {{ $record->email }}
                </a>
            </td>
        </tr>
        <tr class="h-[27.4pt] border border-black">
            <td class="font-manrope w-[91.9pt] bg-gray-300 px-[5.4pt] py-2 align-top font-semibold text-black">
                Jurnal
            </td>
            <td class="font-manrope border border-black bg-gray-200 px-[5.4pt] py-2 align-top">
                {{ $record->journal?->name }}
            </td>
        </tr>
        <tr class="h-[27.2pt] border border-black">
            <td class="font-manrope w-[91.9pt] bg-gray-300 px-[5.4pt] py-2 align-top font-semibold text-black">
                Volume
            </td>
            <td class="font-manrope border border-black bg-gray-200 px-[5.4pt] py-2 align-top">
                {{ $record->volume }}
            </td>
        </tr>
        <tr class="h-[37.5pt] border border-black">
            <td class="font-manrope w-[91.9pt] bg-gray-300 px-[5.4pt] py-2 align-top font-semibold text-black">
                Link Terbitan
            </td>
            <td class="font-manrope border border-black bg-gray-200 px-[5.4pt] py-2 align-top">
                <a href="http://"
                   class="hover:text-primary transition-colors">
                    {{ $record->publication_link }}
                </a>
            </td>
        </tr>
    </table>
    <div class="pt-5">
        <p class="font-manrope text-justify text-[10pt] font-normal leading-relaxed text-black/80">
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
        <p class="font-manrope mb-4 text-[10pt] font-bold text-black">
            Malang, {{ $record->approved_date?->format('d F Y') }}
        </p>
        <div class="mb-4">
            <img alt="Signature"
                 src="{{ asset('assets/qrcode.png') }}"
                 class="h-[73.33px] w-[73.33px] object-contain" />
        </div>
        <p class="font-manrope text-[11pt] font-bold leading-tight text-black">
            Dr. Umam Rofiq, M.Pd., Ph.D
        </p>
        <p class="font-manrope font-bold text-black">Director</p>
    </div>
</body>

</html>
