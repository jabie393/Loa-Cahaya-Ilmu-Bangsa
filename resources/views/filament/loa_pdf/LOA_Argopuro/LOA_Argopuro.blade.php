<html>

<head>
    <link rel="preconnect"
          href="https://fonts.googleapis.com" />
    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Baumans&display=swap"
          rel="stylesheet" />
    <meta content="text/html; charset=UTF-8"
          http-equiv="content-type" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        baumans: ["Baumans", "system-ui"],
                        bahnschrift: ["Bahnschrift", "sans-serif"],
                        calibri: ["Calibri", "sans-serif"],
                        playfair: ['"Playfair Display"', "serif"],
                    },
                },
            },
        };
    </script>
    <style type="text/tailwindcss">
        @import url(https://themes.googleusercontent.com/fonts/css?kit=1n8us-eEzYYhbTLMubR0ZA1KNGra13VIU0iL8fHG2AX-SYK_Ln3uQOimoUBe-l5o);

        /* Base layer fully removed; cascading utilities set on body instead */
        @page {
            size: A4;
            margin: 0;
        }

        @media print {
            body {
                @apply bg-white;
            }

            .print-a4 {
                margin: 0px !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>

<body class="font-calibri print-a4 mx-auto my-[10mm] box-border max-h-[297mm] w-[210mm] bg-white px-[72pt] pb-[72pt] pt-[105.8pt] text-[11pt] text-black shadow-[0_0_10px_rgba(0,0,0,0.2)]">
    <span class="absolute z-[-10] inline-block h-[639.5px] w-[639.5px] overflow-hidden opacity-10">
        <img alt=""
             src="{{ asset('assets/logo.png') }}"
             class="h-[639.5px] w-[639.5px]"
             title="" />
    </span>
    <div class="absolute top-20 flex w-[165mm] justify-between">
        <div>
            <p class="font-baumans text-[35pt] font-bold">ARGOPURO</p>
        </div>
        <div class="flex">
            <div>
                <div class="text-right">
                    <p class="font-bahnschrift text-[12pt] font-bold">
                        CAHAYA ILMU BANGSA INSTITUTE
                    </p>
                    <p class="font-bahnschrift text-[9pt] font-bold">
                        KEMENKUMHAM AHU-0018912-AH.01.14
                    </p>
                    <p class="font-bahnschrift text-[7pt]">
                        Biro Penelitian, Publikasi, dan Pengabdian Kepada
                        Masyarakat
                    </p>
                    <p class="font-bahnschrift text-[7pt]">
                        Perum Puri Kartika Asri Blok 2 A2 Malang
                    </p>
                    <p class="font-bahnschrift text-[7pt]">
                        e-mail:
                        <a href="mailto:admin@cahayailmubangsa.institute">
                            admin@cahayailmubangsa.institute
                        </a>
                    </p>
                </div>
            </div>
            <div>
                <img src="{{ asset('assets/logo.png') }}"
                     class="w-20"
                     alt="" />
            </div>
        </div>
    </div>
    <div class="pt-14">
        <p class="py-0 text-center leading-[1.079]">
            <span class="font-bahnschrift text-[18pt] font-bold text-black">
                LETTER OF ACCEPTANCE
            </span>
        </p>
        <p class="py-0 text-center leading-[1.079]">
            <span class="font-bahnschrift text-[12pt] font-normal text-black">
                NO: 2025/CIB/LOA
            </span>
        </p>
    </div>
    <div class="pb-5 pt-10">
        <p class="py-0 pb-5 text-left leading-[1.079]">
            <span class="font-bahnschrift text-[11pt] font-normal text-black">
                Assalamualaikum Wr. Wb.
            </span>
        </p>
        <p class="py-0 text-left leading-[1.079]">
            <span class="font-bahnschrift text-[11pt] font-normal text-black">
                Bersama surat ini, kami menerangkan bahwa artikel dengan
                keterangan naskah berikut
            </span>
        </p>
    </div>
    <table class="mr-auto border-collapse">
        <tr class="">
            <td class="w-[91.9pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        Judul
                    </span>
                </p>
            </td>
            <td class="w-[14.2pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        :
                    </span>
                </p>
            </td>
            <td class="w-[367.9pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        {{ $record->title }}
                    </span>
                </p>
            </td>
        </tr>
        <tr>
            <td class="w-[91.9pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        Author
                    </span>
                </p>
            </td>
            <td class="w-[14.2pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        :
                    </span>
                </p>
            </td>
            <td class="w-[367.9pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        {{ $record->author_name }}
                    </span>
                </p>
            </td>
        </tr>
        <tr>
            <td class="w-[91.9pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        Instansi
                    </span>
                </p>
            </td>
            <td class="w-[14.2pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        :
                    </span>
                </p>
            </td>
            <td class="w-[367.9pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        {{ $record->institution }}
                    </span>
                </p>
            </td>
        </tr>
        <tr>
            <td class="w-[91.9pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        Korespondensi
                    </span>
                </p>
            </td>
            <td class="w-[14.2pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        :
                    </span>
                </p>
            </td>
            <td class="w-[367.9pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <a href="mailto:[EMAIL_ADDRESS]"
                       class="font-bahnschrift text-[11pt] font-normal text-black">
                        {{ $record->email }}
                    </a>
                </p>
            </td>
        </tr>
        <tr>
            <td class="w-[91.9pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        Jurnal
                    </span>
                </p>
            </td>
            <td class="w-[14.2pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        :
                    </span>
                </p>
            </td>
            <td class="w-[367.9pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        {{ $record->journal->name }}
                    </span>
                </p>
            </td>
        </tr>
        <tr>
            <td class="w-[91.9pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        Volume
                    </span>
                </p>
            </td>
            <td class="w-[14.2pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        :
                    </span>
                </p>
            </td>
            <td class="w-[367.9pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        {{ $record->volume }}
                    </span>
                </p>
            </td>
        </tr>
        <tr>
            <td class="w-[91.9pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        Link Terbitan
                    </span>
                </p>
            </td>
            <td class="w-[14.2pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <span class="font-bahnschrift text-[11pt] font-normal text-black">
                        :
                    </span>
                </p>
            </td>
            <td class="w-[367.9pt] border-0 px-[5.4pt] py-2 align-top"
                colspan="1"
                rowspan="1">
                <p class="py-0 text-left leading-none">
                    <a href="http://"
                       class="font-bahnschrift text-[11pt] font-normal text-black">
                        {{ $record->publication_link }}
                    </a>
                </p>
            </td>
        </tr>
    </table>
    <div class="pb-5 pt-5">
        <p class="pb-[8pt] pt-0 text-left text-justify leading-[1.079]">
            <span class="font-bahnschrift text-[10pt] font-normal text-black">
                Berstatus ACCEPTED untuk dipublish. Keputusan ini dibuat
                sebagai tanda bahwa naskah yang bersangkutan telah lolos
                plagiarism checker. Dan LoA ini dibuat sebagai bukti bahwa
                author telah menyelesaikan APC yang telah ditetapkan oleh
                pengelola jurnal. LOA Berlaku jika dilengkapi link dan pdf
                publish. Hubungi kami di
                admin_jurnal@cahayailmubangsa.institute jika ada pertanyaan
                lebih lanjut, terima kasih.
            </span>
        </p>
    </div>
    <div class="pb-5 pt-5">
        <p class="ml-[318.9pt] pb-[8pt] pt-0 text-left leading-[1.079]">
            <span class="font-bahnschrift text-[11pt] font-normal text-black">
                Malang, {{ $record->approved_date->format('d F Y') }}
            </span>
        </p>
        <p class="ml-[318.9pt] pb-[8pt] pt-0 text-left leading-[1.079]">
            <span class="inline-block h-[73.33px] w-[73.33px] overflow-hidden">
                <img alt=""
                     src="{{ asset('assets/qrcode.png') }}"
                     class="h-[73.33px] w-[73.33px]"
                     title="" />
            </span>
        </p>
        <p class="ml-[318.9pt] py-0 text-left leading-[1.079]">
            <span class="font-playfair text-[11pt] font-bold text-black">
                Danam Priambodo, PhD
            </span>
        </p>
        <p class="ml-[318.9pt] py-0 text-left leading-[1.079]">
            <span class="font-playfair font-bold">Director</span>
        </p>
    </div>
</body>

</html>
