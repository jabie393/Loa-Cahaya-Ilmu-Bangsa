<html>

<head>
    <link rel="preconnect"
          href="https://fonts.googleapis.com" />
    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Baumans&display=swap"
          rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1d428a',
                        secondary: '#930007',
                        background: 'white',
                    },
                    fontFamily: {
                        baumans: ["Baumans", "system-ui"],
                        bahnschrift: ["Bahnschrift", "sans-serif"],
                        calibri: ["Calibri", "sans-serif"],
                        playfair: ['"Playfair Display"', "serif"],
                    },
                },
            },
        };

        window.downloadPDF = async function() {
            const {
                jsPDF
            } = window.jspdf;
            const element = document.querySelector('#capture-area');
            const btn = document.querySelector('button');

            btn.style.opacity = '0.5';
            btn.innerText = 'Processing...';

            try {
                const canvas = await html2canvas(element, {
                    scale: 3,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff',
                    onclone: (clonedDoc) => {
                        clonedDoc.querySelector('#download-btn').style.display = 'none';
                    }
                });

                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: 'a4'
                });

                pdf.addImage(imgData, 'PNG', 0, 0, 210, 297);
                pdf.save(`LOA-{{ $record->author_name }}.pdf`);
            } catch (e) {
                console.error(e);
                window.print();
            } finally {
                btn.style.opacity = '1';
                btn.innerText = 'Download PDF';
            }
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
<button id="download-btn"
        onclick="downloadPDF()"
        class="bg-primary fixed right-8 top-8 z-50 flex items-center gap-2 rounded-xl px-6 py-3 font-bold text-white shadow-2xl transition-transform hover:scale-105 active:scale-95 print:hidden">
    <svg xmlns="http://www.w3.org/2000/svg"
         class="h-5 w-5"
         viewBox="0 0 20 20"
         fill="currentColor">
        <path fill-rule="evenodd"
              d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
              clip-rule="evenodd" />
    </svg>
    Download PDF
</button>

<body class="font-calibri print-a4 mx-auto my-[25mm] box-border max-h-[297mm] w-[210mm] bg-white text-[11pt] text-black shadow-[0_0_10px_rgba(0,0,0,0.2)]">
    <div id="capture-area"
         class="relative h-[297mm] w-[210mm] bg-white px-[72pt] pb-[72pt] pt-[105.8pt]">
        <span class="absolute z-[-10] inline-block h-[639.5px] w-[639.5px] overflow-hidden opacity-10">
            <img alt=""
                 src="{{ asset('assets/logo.png') }}"
                 class="h-[639.5px] w-[639.5px]"
                 title="" />
        </span>
        <div class="relative top-[-60px] flex h-0 w-[165mm] justify-between">
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
                    NO: {{ $record->created_at->format('Y') }}/CIB{{ sprintf('%03d', $record->journal->id) }}/LOA{{ sprintf('%03d', $record->id) }} </span>
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
    </div>
</body>

</html>
