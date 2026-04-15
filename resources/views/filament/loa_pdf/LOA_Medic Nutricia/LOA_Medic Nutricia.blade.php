<html>

<head>
    <link rel="preconnect"
          href="https://fonts.googleapis.com" />
    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400..700;1,400..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
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
                        primary: '#1a365d',
                        secondary: '#0077b6',
                        background: 'white',
                    },
                    fontFamily: {
                        montserrat: ["Montserrat", "sans-serif"],
                        lora: ["Lora", "sans-serif"],
                        calibri: ["Calibri", "sans-serif"],
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

<body class="font-calibri print-a4 bg-background mx-auto my-[25mm] box-border max-h-[297mm] w-[210mm] text-[10pt] text-black shadow-[0_0_10px_rgba(0,0,0,0.2)]">
    <div id="capture-area"
         class="relative h-[297mm] w-[210mm] bg-white px-[72pt] pb-[72pt] pt-[20pt]">
    <div class="bg-primary relative left-[-72pt] top-[-20pt] flex w-[210mm] flex-col justify-between px-[72pt] py-[20pt] text-white">
        <div class="flex justify-between">
            <div>
                <p class="font-montserrat mb-[-4pt] text-[18pt] font-[1000]">
                    CAHAYA ILMU
                    <br />
                    BANGSA INSTITUTE
                </p>
            </div>
            <div class="flex flex-col justify-end text-right">
                <p class="font-montserrat text-[7pt]">
                    KEMENKUMHAM AHU-0018912-AH.01.14
                </p>
                <p class="font-montserrat text-[7pt]">
                    Perum Puri Kartika Asri Blok 2 A2 Malang
                </p>
                <p class="font-montserrat text-[7pt]">
                    e-mail:
                    <a href="mailto:admin@cahayailmubangsa.institute">
                        admin@cahayailmubangsa.institute
                    </a>
                </p>
            </div>
        </div>
        <p class="font-montserrat pb-[5pt] text-[8pt] text-white">
            Biro Penelitian, Publikasi, dan Pengabdian Kepada Masyarakat
        </p>
    </div>

    <div class="flex w-full justify-between">
        <div class="border-primary flex w-full flex-col justify-between border-l-4 pl-5">
            <div>
                <p class="leading-[1.079]">
                    <span class="text-primary font-montserrat text-[18pt] font-bold">
                        LETTER OF ACCEPTANCE
                    </span>
                </p>
                <p class="leading-[1.079]">
                    <span class="text-secondary font-montserrat text-[12pt] font-bold">
                        NO: {{ $record->created_at->format('Y') }}/CIB{{ sprintf('%03d', $record->journal->id) }}/LOA{{ sprintf('%03d', $record->id) }}
                    </span>
                </p>
            </div>
            <div class="flex h-full flex-col justify-center">
                <p class="text-left leading-[1.079]">
                    <span class="font-montserrat text-[12pt] font-bold text-black">
                        Assalamualaikum Wr. Wb.
                    </span>
                </p>
                <p class="text-left">
                    <span class="font-lora text-[10pt] text-gray-700">
                        Bersama surat ini, kami menerangkan bahwa artikel
                        <br />
                        dengan keterangan naskah berikut
                    </span>
                </p>
            </div>
        </div>

        <div class="border-primary border-[1px] border-t-4 bg-[#F0F9FF] px-5 py-3 text-center">
            <img src="{{ asset('assets/cover/Nutricia.jpg') }}"
                 alt=""
                 class="w-[100px]" />
            <p class="text-primary font-montserrat mt-2 text-nowrap text-[8pt] font-bold uppercase">
                Medic Nutricia
            </p>
        </div>
    </div>

    <!-- Content -->
    <div class="m-auto grid grid-cols-2">
        <div class="border-primary col-span-2 mt-5 flex border-2">
            <div class="border-primary flex w-full flex-col border-r-2 p-2">
                <div class="w-full align-top">
                    <p class="text-left">
                        <span class="text-secondary font-montserrat text-[10pt] font-bold uppercase tracking-widest">
                            Author :
                        </span>
                    </p>
                </div>
                <div class="w-full align-top">
                    <p class="text-left">
                        <span class="font-lora text-[10pt] font-normal text-black">
                            {{ $record->author_name }}
                        </span>
                    </p>
                </div>
            </div>
            <div class="flex w-full flex-col p-2">
                <div class="w-full align-top">
                    <p class="text-left">
                        <span class="text-secondary font-montserrat text-[10pt] font-bold uppercase">
                            Korespondensi :
                        </span>
                    </p>
                </div>
                <div class="w-full align-top">
                    <p class="text-left">
                        <a href="mailto:{{ $record->email }}"
                           class="font-lora text-[10pt] font-normal text-black">
                            {{ $record->email }}
                        </a>
                    </p>
                </div>
            </div>
        </div>
        <div class="border-primary col-span-2 border-2 border-t-0 p-2">
            <div class="w-[91.9pt] align-top">
                <p class="text-left">
                    <span class="text-secondary font-montserrat text-[10pt] font-bold uppercase tracking-widest">
                        Instansi :
                    </span>
                </p>
            </div>
            <div class="w-[367.9pt] align-top">
                <p class="text-left">
                    <span class="font-lora text-[10pt] font-normal text-black">
                        {{ $record->institution }}
                    </span>
                </p>
            </div>
        </div>

        <div class="border-primary col-span-2 flex border-2 border-t-0">
            <div class="border-primary flex w-full flex-col border-r-2 p-2">
                <div class="w-full align-top">
                    <p class="text-left">
                        <span class="text-secondary font-montserrat text-[10pt] font-bold uppercase tracking-widest">
                            Jurnal :
                        </span>
                    </p>
                </div>
                <div class="w-full align-top">
                    <p class="text-left">
                        <span class="font-lora text-[10pt] font-normal text-black">
                            {{ $record->journal->name }}
                        </span>
                    </p>
                </div>
            </div>
            <div class="flex w-full flex-col p-2">
                <div class="w-full align-top">
                    <p class="text-left">
                        <span class="text-secondary font-montserrat text-[10pt] font-bold uppercase tracking-widest">
                            Volume :
                        </span>
                    </p>
                </div>
                <div class="w-full align-top">
                    <p class="text-left">
                        <span class="font-lora text-[10pt] font-normal text-black">
                            {{ $record->volume }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="border-primary col-span-2 flex flex-col border-2 border-t-0 p-2">
            <div class="w-full align-top">
                <p class="text-left">
                    <span class="text-secondary font-montserrat text-[10pt] font-bold uppercase tracking-widest">
                        Link Terbitan :
                    </span>
                </p>
            </div>
            <div class="w-full align-top">
                <p class="text-left">
                    <a href="{{ $record->publication_link }}"
                       class="font-lora text-[10pt] font-normal text-black">
                        {{ $record->publication_link }}
                    </a>
                </p>
            </div>
        </div>
        <div class="border-primary col-span-2 mb-5 border-2 border-t-0 p-2">
            <div class="w-[91.9pt] align-top">
                <p class="text-left">
                    <span class="text-secondary font-montserrat text-[10pt] font-bold uppercase">
                        Judul :
                    </span>
                </p>
            </div>
            <div class="w-full align-top">
                <p class="text-left">
                    <span class="font-lora text-[10pt] font-normal text-black">
                        {{ $record->title }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    <div class="pb-5">
        <p class="pb-[8pt] text-left text-justify leading-[1.079]">
            <span class="font-lora text-[10pt] font-normal italic text-black">
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
    <div class="pb-5">
        <p class="ml-[318.9pt] pb-[8pt] pt-0 text-left leading-[1.079]">
            <span class="font-montserrat text-[10pt] font-normal text-black">
                Malang, {{ $record->created_at->format('d F Y') }}
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
        <p class="ml-[318.9pt] text-left leading-[1.079]">
            <span class="font-playfair text-[10pt] font-bold text-black">
                M. Ahmad Sholeh, PhD
            </span>
        </p>
        <p class="ml-[318.9pt] text-left leading-[1.079]">
            <span class="font-playfair font-bold">Director</span>
        </p>
    </div>
    </div>
</body>

</html>
