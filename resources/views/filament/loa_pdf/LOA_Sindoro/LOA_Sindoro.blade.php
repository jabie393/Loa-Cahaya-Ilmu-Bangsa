<html>

<head>
    <link rel="preconnect"
          href="https://fonts.googleapis.com" />
    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:ital,wght@0,100..900;1,100..900&family=Merriweather:ital,opsz,wght@0,18..144,300..900;1,18..144,300..900&display=swap"
          rel="stylesheet" />

    <meta content="text/html; charset=UTF-8"
          http-equiv="content-type" />

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-primary: #f5a623;
            --color-secondary: #708090;
            --color-background: #faf9f6;

            --font-epilogue: "Epilogue", "sans-serif";
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
<body class="font-epilogue print-a4 bg-background mx-auto my-[10mm] box-border max-h-[297mm] w-[210mm] px-[72pt] pb-[72pt] pt-[20pt] text-[10pt] text-black shadow-[0_0_10px_rgba(0,0,0,0.2)]">
    <div class="top-20 flex w-full justify-between">
        <div class="flex w-full flex-col justify-center">
            <div class="flex flex-col">
                <p class="font-epilogue mb-[-2pt] text-[8pt] font-[1000] tracking-[0.4em]">
                    CAHAYA ILMU BANGSA INSTITUTE
                </p>
                <p class="font-epilogue text-[7.5pt] font-bold">
                    Biro Penelitian, Publikasi, dan Pengabdian Kepada
                    Masyarakat
                </p>
            </div>
            <div class="font-epilogue text-secondary text-[8pt]">
                <p class="">KEMENKUMHAM AHU-0018912-AH.01.14</p>
                <p class="">Perum Puri Kartika Asri Blok 2 A2 Malang</p>
                <p>
                    e-mail:
                    <a href="mailto:admin@cahayailmubangsa.institute">
                        admin@cahayailmubangsa.institute
                    </a>
                </p>
            </div>
        </div>
        <div class="flex flex h-[100px] w-[100px] flex-col items-center justify-center text-black">
            <svg viewBox="0 0 16 16"
                 xmlns="http://www.w3.org/2000/svg"
                 version="1.1"
                 fill="none"
                 stroke="currentColor"
                 stroke-linecap="round"
                 stroke-linejoin="round"
                 stroke-width="1.5">
                <g id="SVGRepo_bgCarrier"
                   stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier"
                   stroke-linecap="round"
                   stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                    <path d="m14.25 9.25v-3.25l-6.25-3.25-6.25 3.25 6.25 3.25 3.25-1.5v3.5c0 1-1.5 2-3.25 2s-3.25-1-3.25-2v-3.5"></path>
                </g>
            </svg>
            <h1 class="font-epilogue text-[20pt] font-[1000] uppercase">
                Sindoro
            </h1>
        </div>
    </div>

    <div class="bg-secondary my-3 h-[2px] w-full"></div>

    <div class="flex justify-between">
        <div>
            <div class="bg-primary relative z-[-1] ml-[-96px] h-full w-[150px]"></div>
        </div>
        <h1 class="text-shadow-lg font-epilogue ml-[-250px] text-[20pt] font-[1000] leading-none text-black">
            LETTER
            <br />
            <span class="text-white">OF</span>
            <br />
            ACCEPTANCE
        </h1>
        <p class="font-epilogue h-fit bg-black p-2 text-[12pt] font-bold tracking-[0.3em] text-white">
            NO: {{ $record->created_at->format('Y') }}/CIB{{ sprintf('%03d', $record->journal->id) }}/LOA{{ sprintf('%03d', $record->id) }}
        </p>
    </div>

    <div class="font-merriweather py-5">
        <p class="text-[12pt] font-bold italic text-black">
            Assalamualaikum Wr. Wb.
        </p>
        <p class="text-secondary text-[10pt]">
            Bersama surat ini, kami menerangkan bahwa artikel dengan
            keterangan naskah berikut
        </p>
    </div>

    <!-- Content -->
    <div class="font-merriweather space-y-3">
        <!-- Judul -->
        <div class="flex flex-col">
            <div class="border p-3 pt-0 text-justify leading-relaxed text-black">
                <p class="text-primary relative top-[-6pt] mb-1 w-fit bg-white px-2 text-[7.5pt] font-bold uppercase tracking-wider">
                    Judul
                </p>
                <p>
                    {{ $record->title }}
                </p>
            </div>
        </div>

        <!-- Author -->
        <div class="flex flex-col">
            <div class="border p-3 pt-0 leading-relaxed text-black">
                <p class="text-primary relative top-[-6pt] mb-1 w-fit bg-white px-2 text-[7.5pt] font-bold uppercase tracking-wider">
                    Author
                </p>
                <div class="font-semibold">{{ $record->author_name }}</div>
            </div>
        </div>

        <!-- Instansi -->
        <div class="flex flex-col">
            <div class="border p-3 pt-0 text-justify leading-relaxed text-black">
                <p class="text-primary relative top-[-6pt] mb-1 w-fit bg-white px-2 text-[7.5pt] font-bold uppercase tracking-wider">
                    Instansi
                </p>
                <div>
                    {{ $record->institution }}
                </div>
            </div>
        </div>

        <!-- Korespondensi -->
        <div class="flex flex-col">
            <div class="border p-3 pt-0 leading-relaxed text-black">
                <p class="text-primary relative top-[-6pt] mb-1 w-fit bg-white px-2 text-[7.5pt] font-bold uppercase tracking-wider">
                    Korespondensi
                </p>
                <div class="font-medium">
                    <a href="mailto:{{ $record->email }}"
                       class="text-primary transition-all hover:underline">
                        {{ $record->email }}
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <!-- Jurnal -->
            <div class="flex h-full flex-col">
                <div class="h-full border p-3 pt-0 leading-relaxed text-black">
                    <p class="text-primary relative top-[-6pt] mb-1 w-fit bg-white px-2 text-[7.5pt] font-bold uppercase tracking-wider">
                        Jurnal
                    </p>
                    <div class="text-[9pt] font-medium">
                        {{ $record->journal->name }}
                    </div>
                </div>
            </div>

            <!-- Volume -->
            <div class="flex h-full flex-col">
                <div class="h-full border p-3 pt-0 leading-relaxed text-black">
                    <p class="text-primary relative top-[-6pt] mb-1 w-fit bg-white px-2 text-[7.5pt] font-bold uppercase tracking-wider">
                        Volume
                    </p>
                    <div class="text-[9pt] font-medium">
                        {{ $record->volume }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Link Terbitan -->
        <div class="flex flex-col">
            <div class="border p-5 pt-0 leading-relaxed text-black">
                <p class="text-primary relative top-[-6pt] mb-1 w-fit bg-white px-2 text-[7.5pt] font-bold uppercase tracking-wider">
                    Link Terbitan
                </p>
                <div class="font-medium">
                    <a href="{{ $record->publication_link }}"
                       class="text-primary transition-all hover:underline">
                        {{ $record->publication_link }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="py-5">
        <p class="font-merriweather text-justify text-[10pt] font-normal leading-relaxed text-black/80">
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
    <div class="ml-auto mt-2 w-fit text-left">
        <p class="font-epilogue mb-4 text-[10pt] font-normal text-black">
            Malang, {{ $record->approved_date->format('d F Y') }}
        </p>
        <div class="mb-4">
            <img alt="Signature"
                 src="{{ asset('assets/qrcode.png') }}"
                 class="h-[73.33px] w-[73.33px] object-contain" />
        </div>
        <p class="font-epilogue text-[11pt] font-bold leading-tight text-black">
            Dr. Imam Syafi'i, M.Pd
        </p>
        <p class="font-epilogue font-bold text-black">Director</p>
    </div>
</body>

</html>
