<html>

<head>
    <link rel="preconnect"
          href="https://fonts.googleapis.com" />
    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Manrope:wght@200..800&display=swap"
          rel="stylesheet" />

    <meta content="text/html; charset=UTF-8"
          http-equiv="content-type" />

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-primary: #001851;
            --color-primary-container: #002b7f;
            --color-secondary-fixed: #b5ebff;
            --color-secondary-container: #44d6ff;
            --color-background: #f8f9fa;
            --color-surface-container-low: #f3f4f5;
            --color-surface-container-lowest: #ffffff;
            --color-outline-variant: #c4c6d3;
            --color-on-primary: #ffffff;

            --font-manrope: "Manrope", sans-serif;
            --font-inter: "Inter", sans-serif;
        }

        * {
            border-radius: 0 !important;
        }

        .architectural-shadow {
            box-shadow: 0 20px 40px rgba(0, 24, 81, 0.06);
        }

        @page {
            size: A4;
            margin: 0;
        }

        @media print {
            body {
                background-color: white;
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
<body class="font-inter print-a4 bg-surface-container-low architectural-shadow relative mx-auto my-[10mm] box-border max-h-[297mm] w-[210mm] px-[72pt] pb-[72pt] pt-[20pt] text-[10pt] text-black">
    <!-- Geometric Shard Background -->
    <div class="bg-secondary-container absolute right-0 top-0 -z-10 h-[400px] w-[400px] opacity-5"
         style="clip-path: polygon(100% 0, 0 0, 100% 100%)"></div>
    <div class="bg-primary absolute bottom-0 left-0 -z-10 h-[500px] w-[500px] opacity-5"
         style="clip-path: polygon(0 0, 0% 100%, 100% 100%)"></div>

    <div class="relative z-10 flex w-full justify-between">
        <div class="flex-1">
            <h1 class="text-primary font-manrope text-[30pt] font-extrabold leading-[0.9] tracking-tighter">
                LETTER
                <br />
                <span class="text-transparent"
                      style="-webkit-text-stroke: 1.5px var(--color-primary)">
                    OF
                </span>
                <br />
                ACCEPTANCE
            </h1>
        </div>
        <div class="flex w-full flex-col justify-center text-right">
            <div class="flex flex-col">
                <p class="font-manrope text-primary mb-[-2pt] text-[8pt] font-extrabold tracking-[0.4em]">
                    CAHAYA ILMU BANGSA INSTITUTE
                </p>
                <p class="font-manrope text-primary/70 text-[7.5pt] font-bold">
                    Biro Penelitian, Publikasi, dan Pengabdian Kepada
                    Masyarakat
                </p>
            </div>
            <div class="font-inter text-primary/60 mt-2 text-[8pt]">
                <p class="">KEMENKUMHAM AHU-0018912-AH.01.14</p>
                <p class="">Perum Puri Kartika Asri Blok 2 A2 Malang</p>
                <p>
                    e-mail:
                    <a href="mailto:admin@cahayailmubangsa.institute"
                       class="text-primary font-semibold hover:underline">
                        admin@cahayailmubangsa.institute
                    </a>
                </p>
            </div>
        </div>
    </div>

    <div class="bg-primary/10 my-3 h-[1px] w-full"></div>

    <div class="flex h-fit items-center justify-between">
        <div class="flex h-[100px] flex-col justify-end">
            <p class="text-primary mb-2 text-[14pt] font-bold italic">
                Assalamualaikum Wr. Wb.
            </p>
        </div>
        <div class="flex flex-col items-end gap-2 text-right">
            <div class="text-primary font-manrope text-[7pt] font-bold uppercase tracking-widest">
                Liberosis : Jurnal Psikologi dan Bimbingan Konseling
            </div>
            <div class="bg-secondary-container h-1 w-full"></div>
            <div class="bg-primary font-manrope p-2 text-[11pt] font-bold tracking-widest text-white">
                NO: {{ $record->created_at->format('Y') }}/CIB{{ sprintf('%03d', $record->journal->id) }}/LOA{{ sprintf('%03d', $record->id) }}
            </div>
        </div>
    </div>

    <div class="font-inter relative z-10">
        <div class="bg-secondary-container mb-2 h-px w-24"></div>
        <p class="text-primary/70 max-w-prose text-[10pt] leading-relaxed">
            Bersama surat ini, kami menerangkan bahwa artikel dengan
            keterangan naskah berikut
        </p>
    </div>
    <div class="font-merriweather py-5">
        <!-- Content Blocks -->
        <div class="font-inter relative z-10 space-y-2">
            <!-- Judul -->
            <div class="bg-surface-container-lowest architectural-shadow border-primary flex flex-col border-l-4">
                <div class="p-2 pt-1">
                    <span class="text-primary/50 block py-2 text-[7pt] font-bold uppercase tracking-[0.2em]">
                        Judul Artikel
                    </span>
                    <p class="text-primary break-words text-justify text-[11pt] font-bold leading-tight">
                        {{ $record->title }}
                    </p>
                </div>
            </div>

            <!-- Author -->
            <div class="bg-surface-container-lowest architectural-shadow border-primary flex flex-col border-l-4">
                <div class="p-2 pt-1">
                    <span class="text-primary/50 block py-2 text-[7pt] font-bold uppercase tracking-[0.2em]">
                        Author
                    </span>
                    <div class="text-primary text-[10pt] font-semibold uppercase tracking-wide">
                        {{ $record->author_name }}
                    </div>
                </div>
            </div>

            <!-- Instansi -->
            <div class="bg-surface-container-lowest architectural-shadow border-primary flex flex-col border-l-4">
                <div class="p-2 pt-1">
                    <span class="text-primary/50 block py-2 text-[7pt] font-bold uppercase tracking-[0.2em]">
                        Instansi
                    </span>
                    <div class="text-primary/80 break-words text-[10pt] leading-relaxed">
                        {{ $record->institution }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <!-- Korespondensi -->
                <div class="bg-surface-container-lowest architectural-shadow border-primary flex h-full flex-col border-l-4">
                    <div class="p-2 pt-1">
                        <span class="text-primary/50 block py-2 text-[7pt] font-bold uppercase tracking-[0.2em]">
                            Korespondensi
                        </span>
                        <div class="text-primary break-all font-medium">
                            <a href="mailto:{{ $record->email }}"
                               class="hover:underline">
                                {{ $record->email }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Jurnal -->
                <div class="bg-surface-container-lowest architectural-shadow border-primary flex h-full flex-col border-l-4">
                    <div class="p-2 pt-1">
                        <span class="text-primary/50 block py-2 text-[7pt] font-bold uppercase tracking-[0.2em]">
                            Jurnal
                        </span>
                        <div class="text-primary text-[9pt] font-bold">
                            {{ $record->journal->name }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <!-- Volume -->
                <div class="bg-surface-container-lowest architectural-shadow border-primary flex h-full flex-col border-l-4">
                    <div class="p-2 pt-1">
                        <span class="text-primary/50 block py-2 text-[7pt] font-bold uppercase tracking-[0.2em]">
                            Volume
                        </span>
                        <div class="text-primary/80 text-[9pt] font-medium">
                            {{ $record->volume }}
                        </div>
                    </div>
                </div>

                <!-- Link Terbitan -->
                <div class="bg-surface-container-lowest architectural-shadow border-primary flex h-full flex-col border-l-4">
                    <div class="p-2 pt-1">
                        <span class="text-primary/50 block py-2 text-[7pt] font-bold uppercase tracking-[0.2em]">
                            Link Terbitan
                        </span>
                        <div class="text-primary break-all font-medium">
                            <a href="http://"
                               class="text-[9pt] lowercase italic hover:underline">
                                {{ $record->publication_link }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="relative z-10 py-2">
            <div class="bg-primary/5 p-3">
                <p class="text-primary font-inter text-justify text-[9pt] font-medium italic leading-relaxed">
                    Berstatus ACCEPTED untuk dipublish. Keputusan ini dibuat
                    sebagai tanda bahwa naskah yang bersangkutan telah lolos
                    plagiarism checker. Dan LoA ini dibuat sebagai bukti
                    bahwa author telah menyelesaikan APC yang telah
                    ditetapkan oleh pengelola jurnal. LOA Berlaku jika
                    dilengkapi link dan pdf publish. Hubungi kami di
                    <a href="mailto:admin_jurnal@cahayailmubangsa.institute"
                       class="text-primary font-semibold">
                        admin_jurnal@cahayailmubangsa.institute
                    </a>
                    jika ada pertanyaan lebih lanjut, terima kasih.
                </p>
            </div>
        </div>

        <div class="border-primary/10 mt-2 flex items-end justify-end border-t pt-2">
            <div class="text-center">
                <p class="text-primary/70 font-inter mb-4 text-[10pt] font-semibold">
                    Malang, {{ $record->created_at->format('d F Y') }}
                </p>
                <div class="mb-4 flex justify-end pr-10">
                    <img alt="Signature"
                         src="{{ asset('assets/qrcode.png') }}"
                         class="h-[80px] w-[80px] object-contain mix-blend-multiply" />
                </div>
                <p class="font-manrope text-primary text-[12pt] font-extrabold leading-tight">
                    Dr. Ahmad Zaini, M.Pd
                </p>
                <p class="font-manrope text-secondary-container text-[8pt] font-bold uppercase tracking-widest">
                    Director
                </p>
            </div>
        </div>
    </div>
</body>

</html>
