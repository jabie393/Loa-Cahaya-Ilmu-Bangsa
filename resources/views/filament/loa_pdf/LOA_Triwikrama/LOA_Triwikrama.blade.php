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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0095e2',
                        secondary: '#1d428a',
                        background: '#faf9f6',
                    },
                    fontFamily: {
                        space: ["Space Grotesk", "sans-serif"],
                    },
                },
            },
        };

        window.downloadPDF = async function() {
            const {
                jsPDF
            } = window.jspdf;
            const element = document.querySelector('#capture-area');
            const btn = document.querySelector('#download-btn');

            btn.style.opacity = '0.5';
            btn.innerText = 'Processing...';

            try {
                const canvas = await html2canvas(element, {
                    scale: 3,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff'
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
        @page {
            size: A4;
            margin: 0;
            max-height: A4;
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

<body class="font-space print-a4 bg-background mx-auto my-[25mm] box-border max-h-[297mm] w-[210mm] text-[10pt] text-black shadow-[0_0_10px_rgba(0,0,0,0.2)]">
    <div id="capture-area"
         class="relative h-[297mm] w-[210mm] bg-white px-[72pt] pb-[72pt] pt-[20pt]">
    <div class="top-20 flex w-full flex-col justify-between py-5">
        <p class="font-space mb-[2pt] text-[20pt] font-[1000]">
            CAHAYA ILMU BANGSA INSTITUTE
        </p>
        <p class="font-manrope text-[7pt] font-bold">
            Biro Penelitian, Publikasi, dan Pengabdian Kepada Masyarakat
        </p>
        <div class="bg-primary my-2 w-full"></div>
        <div class="flex justify-between">
            <div>
                <h2 class="font-space text-[20pt] font-bold">
                    Triwikrama
                </h2>
                <p>Jurnal Ilmu Sosial</p>
            </div>
            <div class="text-right">
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

    <div class="flex flex-col items-center text-center">
        <h1 class="font-space text-[20pt] font-[1000] leading-none text-black">
            LETTER OF ACCEPTANCE
        </h1>
        <div class="bg-primary my-2 h-[10px] w-[300px]"></div>
        <p class="text-secondary font-space text-[12pt] tracking-[0.3em]">
            NO: {{ $record->created_at->format('Y') }}/CIB{{ sprintf('%03d', $record->journal->id) }}/LOA{{ sprintf('%03d', $record->id) }}
        </p>
    </div>

    <div class="py-5">
        <p class="text-secondary font-bold italic">
            Assalamualaikum Wr. Wb.
        </p>
        <p class="font-space text-[10pt] text-gray-700">
            Bersama surat ini, kami menerangkan bahwa artikel dengan
            keterangan naskah berikut
        </p>
    </div>

    <!-- Content -->
    <table class="w-full border-collapse text-[10pt]">
        <tr class="border-primary h-[55pt] border-l-4">
            <td class="font-space text-primary w-[91.9pt] px-[5.4pt] py-4 align-top font-semibold uppercase">
                Judul
            </td>
            <td class="font-space px-[5.4pt] py-4 align-top">
                {{ $record->title }}
            </td>
        </tr>
        <tr class="border-primary h-[28.1pt] border-l-4">
            <td class="font-space text-primary w-[91.9pt] px-[5.4pt] py-2 align-top font-semibold uppercase">
                Author
            </td>
            <td class="font-space px-[5.4pt] py-2 align-top">
                {{ $record->author_name }}
            </td>
        </tr>
        <tr class="border-primary h-[56pt] border-l-4">
            <td class="font-space text-primary w-[91.9pt] px-[5.4pt] py-4 align-top font-semibold uppercase">
                Instansi
            </td>
            <td class="font-space px-[5.4pt] py-4 text-justify align-top">
                {{ $record->institution }}
            </td>
        </tr>
        <tr class="border-primary h-[29.4pt] border-l-4">
            <td class="font-space text-primary w-[91.9pt] px-[5.4pt] py-2 align-top font-semibold uppercase">
                Korespondensi
            </td>
            <td class="font-space px-[5.4pt] py-2 align-top">
                <a href="mailto:{{ $record->email }}"
                   class="hover:text-primary transition-colors">
                    {{ $record->email }}
                </a>
            </td>
        </tr>
        <tr class="border-primary h-[27.4pt] border-l-4">
            <td class="font-space text-primary w-[91.9pt] px-[5.4pt] py-2 align-top font-semibold uppercase">
                Jurnal
            </td>
            <td class="font-space px-[5.4pt] py-2 align-top">
                {{ $record->journal->name }}
            </td>
        </tr>
        <tr class="border-primary h-[27.2pt] border-l-4">
            <td class="font-space text-primary w-[91.9pt] px-[5.4pt] py-2 align-top font-semibold uppercase">
                Volume
            </td>
            <td class="font-space px-[5.4pt] py-2 align-top">
                {{ $record->volume }}
            </td>
        </tr>
        <tr class="border-primary h-[37.5pt] border-l-4">
            <td class="font-space text-primary w-[91.9pt] px-[5.4pt] py-2 align-top font-semibold uppercase">
                Link Terbitan
            </td>
            <td class="font-space px-[5.4pt] py-2 align-top">
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
            Dr. Hilmi Yahya, M.Pd., PhD
        </p>
        <p class="font-space font-bold text-black">Director</p>
    </div>
    </div>
</body>

</html>
