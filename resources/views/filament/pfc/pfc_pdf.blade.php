<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0"
          name="viewport" />
    <title>Plagiarism-Free Certificate - {{ $record->author_name }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com"
          rel="preconnect" />
    <link crossorigin=""
          href="https://fonts.gstatic.com"
          rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&amp;family=Great+Vibes&amp;family=Montserrat:wght@300;400&amp;display=swap"
          rel="stylesheet" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "cert-navy": "#1a365d",
                        "cert-gold": "#c5a059",
                        "cert-blue-light": "#3b82f6",
                    },
                    fontFamily: {
                        serif: ['"Libre Baskerville"', "serif"],
                        script: ['"Great Vibes"', "cursive"],
                        sans: ["Montserrat", "sans-serif"],
                    },
                },
            },
        };
    </script>
    <style type="text/tailwindcss">
        @page {
            size: portrait;
            margin: 0;
        }

        @media print {
            body {
                background-color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                height: 100vh !important;
                width: 100vw !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .print-a4 {
                margin: 0 !important;
                box-shadow: none !important;
                padding: 0 !important;
                width: 210mm !important;
                height: 297mm !important;
                border: none !important;
            }
        }

        .certificate-page {
            width: 210mm;
            height: 297mm;
            background-color: white;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            position: relative;
            overflow: hidden;
        }

        .dot-line {
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 12px 12px;
            width: 12px;
        }

        .decorative-band {
            height: 12%;
            width: 100%;
            position: absolute;
            left: 0;
            pointer-events: none;
        }
    </style>
</head>

<body class="max-h-landscape flex h-fit min-h-screen w-full items-center justify-center bg-white lg:bg-gray-200 lg:p-10 print:bg-white print:p-0">
    <button onclick="window.print()"
            class="bg-cert-navy fixed bottom-8 right-8 z-[100] flex items-center gap-2 rounded-xl px-6 py-3 font-bold text-white shadow-2xl transition-transform hover:scale-105 active:scale-95 print:hidden">
        <svg xmlns="http://www.w3.org/2000/svg"
             class="h-5 w-5"
             viewBox="0 0 20 20"
             fill="currentColor">
            <path fill-rule="evenodd"
                  d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                  clip-rule="evenodd" />
        </svg>
        Download PFC
    </button>

    <main class="certificate-page print-a4 text-cert-navy relative flex flex-col items-center justify-center px-16 pb-48 pt-40"
          data-purpose="certificate-main-layout">
        <div class="decorative-band top-0"
             data-purpose="header-graphics">
            <svg class="absolute left-0 top-0 h-full w-full"
                 preserveAspectRatio="none"
                 viewBox="0 0 800 100">
                <path d="M0 0 H800 V60 Q600 90 400 40 Q200 -10 0 70 Z"
                      fill="#1a365d"></path>
                <path d="M0 0 H800 V40 Q600 70 400 20 Q200 -30 0 50 Z"
                      fill="#2d3748"
                      opacity="0.3"></path>
            </svg>
            <div class="bg-cert-gold absolute left-1/2 top-1/2 z-20 h-14 w-14 -translate-x-1/2 -translate-y-1/2 rotate-45 transform border-4 border-white shadow-lg"></div>
        </div>
        <div class="dot-line absolute bottom-40 left-12 top-40 opacity-50"></div>
        <div class="dot-line absolute bottom-40 right-12 top-40 opacity-50"></div>
        <section class="relative z-10 flex flex-col items-center text-center justify-center"
                 data-purpose="certificate-content">
            <div class="mb-10">
                <h1 class="font-serif text-5xl font-bold uppercase leading-tight tracking-widest">
                    Certificate
                </h1>
                <h2 class="font-serif text-2xl tracking-[0.3em]">
                    BEBAS PLAGIARISME
                </h2>
                <p class="mt-4 font-serif text-sm tracking-widest">
                    No : {{ $record->created_at->format('Y') }}/CIB{{ sprintf('%03d', $record->journal->id) }}/PFC{{ sprintf('%03d', $record->id) }}
                </p>
            </div>
            <div class="border-cert-navy/20 w-full max-w-xl border-b pb-4">
                <p class="mb-6 font-serif text-lg font-bold uppercase tracking-wide">
                    Diberikan kepada
                </p>
                <p class="recipient-name text-black font-serif text-2xl">
                    {{ $record->author_name }}
                </p>
            </div>
            <div class="my-5 w-full max-w-xl">
                <p class="text-md mb-2 font-serif font-bold uppercase tracking-wide">
                    Telah Menulis artikel ilmiah dengan judul :
                </p>
                <p class="recipient-name text-black font-serif text-lg">
                    {{ $record->title }}
                </p>
            </div>
            <div class="mb-5 w-full max-w-xl">
                <p class="text-md mb-2 font-serif font-bold uppercase tracking-wide">
                    Pada Jurnal :
                </p>
                <p class="recipient-name text-black font-serif text-lg">
                    {{ $record->journal->name }}
                </p>
            </div>
            <div class="mx-auto max-w-lg space-y-8">
                <p class="text-justify font-serif text-base italic leading-relaxed text-gray-700">
                    Kepandanya diberikan predikat sangat baik dengan plagiasi yang berlaku pada saat jurnal ilmiah diterbitkan denganpersantase dibawah 30% Sertifikat bebas plagiarisme ini diberikan dan berlaku jika dilengkapi dengan LoA penerimaan. Demikian sertifikat ini dibuat untuk digunakan sebagaimana mestinya.
                </p>
            </div>
        </section>

        <div class="relative z-10 mt-12 flex w-full justify-center px-16"
             data-purpose="signatures">
            <div class="flex flex-col items-center">
                <p class="mb-2 font-serif text-sm tracking-widest">
                    Telah ditandatangani secara elektronik
                </p>
                <img src="{{ asset('assets/qrcode.png') }}"
                     alt=""
                     class="h-16 w-16 opacity-80" />
                <div class="border-cert-navy mb-2 w-40 border-t-2"></div>
                <p class="font-serif text-sm">IWAN PAHLEVI</p>
            </div>
        </div>

        <div class="decorative-band bottom-0"
             data-purpose="footer-graphics">
            <svg class="absolute bottom-0 left-0 h-full w-full"
                 preserveAspectRatio="none"
                 viewBox="0 0 800 100">
                <path d="M0 100 H800 V40 Q600 10 400 60 Q200 110 0 30 Z"
                      fill="#1a365d"></path>
                <path d="M0 100 H800 V60 Q600 30 400 80 Q200 130 0 50 Z"
                      fill="#c5a059"
                      opacity="0.2"></path>
            </svg>
            <div class="bg-cert-gold absolute bottom-1/2 left-1/2 z-20 h-14 w-14 -translate-x-1/2 translate-y-1/2 rotate-45 transform border-4 border-white shadow-lg"></div>
        </div>
    </main>
</body>

</html>
