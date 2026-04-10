<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0"
          name="viewport" />
    <title>Certificate of Achievement - Juliana Silva</title>
    <!-- Tailwind CSS CDN with plugins -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Google Fonts for Typography -->
    <link href="https://fonts.googleapis.com"
          rel="preconnect" />
    <link crossorigin=""
          href="https://fonts.gstatic.com"
          rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&amp;family=Montserrat:wght@400;700;800&amp;family=Playfair+Display:ital,wght@0,400;0,700;1,400&amp;display=swap"
          rel="stylesheet" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "cert-navy": "#003354",
                        "cert-gold": "#D4AF37",
                        "cert-blue-light": "#005b8e",
                    },
                    fontFamily: {
                        sans: ["Montserrat", "sans-serif"],
                        serif: ["Playfair Display", "serif"],
                        script: ["Great Vibes", "cursive"],
                    },
                },
            },
        };
    </script>
    <style type="text/tailwindcss">
        @page {
            size: landscape;
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
                background-image: none !important;
                width: 297mm !important;
                height: 210mm !important;
                border: none !important;
            }
        }

        /* Subtle geometric background pattern */
        .bg-pattern {
            background-color: #ffffff;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0l30 30-30 30L0 30z' fill='%23f0f0f0' fill-opacity='0.4' fill-rule='evenodd'/%3E%3C/svg%3E");
        }
    </style>

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
        Download Certificate
    </button>
    <style data-purpose="layout-refinement">
        .certificate-container {
            width: 1100px;
            height: 770px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* Decorative Corner Elements using CSS clipping/shapes or SVG positioning */
        .corner-top-left {
            position: absolute;
            top: 0;
            left: 0;
            width: 400px;
            height: 400px;
            z-index: 10;
        }

        .corner-bottom-right {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 400px;
            height: 400px;
            z-index: 10;
            transform: rotate(180deg);
        }
    </style>
</head>

<body class="max-h-landscape flex h-fit min-h-screen w-fit w-full items-center justify-center bg-white lg:bg-gray-200 lg:p-10 print:bg-white print:p-0">
    <!-- BEGIN: Certificate Layout -->
    <main class="certificate-container bg-pattern print-a4 relative border-[12px] border-white bg-white"
          data-purpose="main-certificate-frame">
        <!-- BEGIN: Decorative Corners -->
        <!-- Top Left Corner -->
        <div class="corner-top-left">
            <svg fill="none"
                 viewbox="0 0 400 400"
                 xmlns="http://www.w3.org/2000/svg">
                <path d="M0 0H400L0 250V0Z"
                      fill="#003354"></path>
                <path d="M0 0H280L0 180V0Z"
                      fill="#005b8e"></path>
                <path d="M100 0L0 130V150L120 0H100Z"
                      fill="#D4AF37"></path>
                <path d="M300 0L0 380V400L320 0H300Z"
                      fill="#D4AF37"></path>
            </svg>
        </div>
        <!-- Bottom Right Corner (reused and rotated via CSS) -->
        <div class="corner-bottom-right">
            <svg fill="none"
                 viewbox="0 0 400 400"
                 xmlns="http://www.w3.org/2000/svg">
                <path d="M0 0H400L0 250V0Z"
                      fill="#003354"></path>
                <path d="M0 0H280L0 180V0Z"
                      fill="#005b8e"></path>
                <path d="M100 0L0 130V150L120 0H100Z"
                      fill="#D4AF37"></path>
                <path d="M300 0L0 380V400L320 0H300Z"
                      fill="#D4AF37"></path>
            </svg>
        </div>
        <!-- END: Decorative Corners -->
        <!-- BEGIN: Certificate Content -->
        <div class="relative z-20 flex h-full flex-col items-center justify-center px-24 py-16 text-center">
            <!-- Header Section -->
            <header class="mb-6">
                <h1 class="text-cert-navy mb-2 text-6xl font-extrabold uppercase tracking-widest">
                    Certificate
                </h1>
                <h2 class="text-cert-navy text-3xl font-bold uppercase tracking-[0.3em]">
                    Penghargaan
                </h2>
                <h2 class="text-cert-navy text-lg font-bold uppercase tracking-[0.3em]">
                    No : {{ $record->created_at->format('Y') }}/CIB{{ sprintf('%03d', $record->journal->id) }}/AC{{ sprintf('%03d', $record->id) }}
                </h2>
            </header>
            <!-- Presentation Text -->
            <p class="mb-8 font-serif text-xl italic text-gray-800">
                Diberikan Kepada :
            </p>
            <!-- Recipient Name -->
            <div class="mb-8">
                <h3 class="text-cert-navy font-serif text-4xl leading-tight">
                    {{ $record->author_name }}
                </h3>
            </div>
            <!-- Achievement Description -->
            <div class="mb-10 max-w-3xl">
                <p class="text-justify text-lg font-bold leading-relaxed text-gray-900">
                    Telah mempublikasikan karya tulis ilmiah pada jurnal
                    yang diterbitkan oleh Cahaya Ilmu Bangsa Institute SK
                    KEMENKUMHAM AHU-0018912-AH.01.14. Demikian Sertifikat
                    ini dibuat untuk dipergunakan sebagaimana mestinya.
                </p>
            </div>

            <!-- Signatures Section -->
            <footer class="mt-auto flex w-full flex-col items-center justify-center px-12">
                <p class="pb-5 text-justify text-lg font-bold leading-relaxed text-gray-900">
                    Telah ditanda-tangani secara online
                </p>

                <!-- Manager Signature -->
                <div class="flex w-64 flex-col items-center">
                    <img src="{{ asset('assets/qrcode.png') }}"
                         alt="" class="w-20 h-20"/>
                    <div class="border-cert-gold my-2 w-full border-b-2">
                    </div>
                    <p class="text-lg font-bold text-gray-800">
                        Buyung Nasution. Ph.D
                    </p>
                </div>
            </footer>
        </div>
        <!-- END: Certificate Content -->
    </main>
    <!-- END: Certificate Layout -->
</body>

</html>
