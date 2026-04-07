<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>LOA | Panduan Pengajuan & Verifikasi</title>

    <!-- Fonts -->
    <link rel="preconnect"
          href="https://fonts.googleapis.com">
    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
          rel="stylesheet">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .editorial-gradient {
            background: linear-gradient(135deg, #004ac6 0%, #2563eb 100%);
        }

        .hero-gradient {
            background: linear-gradient(135deg, #f8f9fa 0%, #eef2f7 50%, #e0e7ff 100%);
        }
    </style>
</head>

<body class="bg-surface font-body text-on-surface antialiased">
    <!-- TopNavBar -->
    <nav class="fixed top-0 z-50 w-full bg-white/80 shadow-[0_20px_40px_rgba(0,74,198,0.05)] backdrop-blur-xl dark:bg-slate-950/80">
        <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-8 py-4">
            <div class="font-headline text-xl font-bold tracking-tighter text-slate-900 dark:text-white">
                <a href="{{ url('/') }}"
                   class="flex items-center gap-2">
                    <img src="https://aset.warunayama.org/images/logo.png"
                         alt=""
                         class="h-8 w-8">
                    <span>LOA</span>
                </a>
            </div>
            <div class="font-headline hidden items-center gap-8 text-sm font-medium tracking-tight transition duration-150 ease-in-out md:flex">
                <a class="btn btn-ghost rounded-2xl transition-all duration-300 hover:border-b-2 hover:border-blue-600 hover:pb-1 hover:font-bold hover:text-blue-700 dark:border-blue-400 dark:text-blue-400"
                   href="#">Home</a>
                <a class="btn btn-ghost rounded-2xl transition-all duration-300 hover:border-b-2 hover:border-blue-600 hover:pb-1 hover:font-bold hover:text-blue-700 dark:border-blue-400 dark:text-blue-400"
                   href="#">LOA Guide</a>
            </div>
            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                    <div class="flex items-center gap-4">
                        @auth
                            <a href="/dashboard"
                               class="bg-primary text-on-primary shadow-primary/20 font-headline scale-95 rounded-xl px-6 py-2.5 text-sm font-semibold shadow-lg transition-transform active:scale-90">
                                Dashboard
                            </a>
                        @else
                            <a href="/login"
                               class="font-headline hover:text-primary text-sm font-bold text-slate-600 dark:text-slate-400">
                                Log in
                            </a>

                            @if (Route::has('register'))
                                <a href="/register"
                                   class="bg-primary text-on-primary shadow-primary/20 font-headline scale-95 rounded-xl px-6 py-2.5 text-sm font-semibold shadow-lg transition-transform active:scale-90">
                                    Register
                                </a>
                            @endif
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <main>
        <!-- Hero Section -->
        <section class="hero-gradient mt-18">
            <div class="mx-auto flex flex w-full max-w-7xl flex-col items-center items-center justify-center justify-center px-8 py-20 text-center md:w-3/5">
                <span class="bg-primary/10 text-primary mb-6 inline-block rounded-full px-4 py-1.5 text-xs font-bold uppercase tracking-widest">Editorial Service</span>
                <h1 class="font-headline text-on-surface mb-6 text-6xl font-extrabold leading-tight tracking-tighter md:text-7xl">
                    Panduan Pengajuan <br /> & Verifikasi
                </h1>
                <p class="text-on-surface-variant mb-10 max-w-xl text-xl leading-relaxed">
                    Sistem kurasi digital untuk mempermudah perolehan Letter of Acceptance (LOA) bagi civitas akademika dengan standar editorial profesional.
                </p>
                <div class="flex items-center gap-6">
                    <a href="/register"
                       class="editorial-gradient font-headline hover:shadow-primary/30 flex items-center gap-2 rounded-xl px-8 py-4 text-lg font-bold text-white transition-all hover:shadow-xl">
                        Ajukan LOA Sekarang
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- Alur Pengajuan LOA (Process Flow) -->
        <section class="bg-surface-container-low/50 py-24">
            <div class="mx-auto max-w-7xl px-8">
                <div class="mx-auto mb-16 max-w-2xl text-center">
                    <h2 class="font-headline text-on-surface mb-4 text-4xl font-bold tracking-tight">Alur Proses Kurasi</h2>
                    <p class="text-on-surface-variant">Ikuti langkah-langkah terukur untuk mendapatkan verifikasi resmi Letter of Acceptance Anda.</p>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4 md:gap-0">
                    <!-- Step 1 -->
                    <div class="bg-surface-container-lowest group relative flex flex-col items-center rounded-2xl p-8 text-center md:rounded-none md:bg-transparent">
                        <div class="editorial-gradient shadow-primary/20 font-headline z-10 mb-6 flex h-16 w-16 items-center justify-center rounded-full text-2xl font-bold text-white shadow-xl transition-transform group-hover:scale-110">1</div>
                        <div class="bg-outline-variant/30 absolute left-1/2 top-16 hidden h-0.5 w-full md:block"></div>
                        <h3 class="font-headline mb-3 text-lg font-bold">Submit Artikel</h3>
                        <p class="text-on-surface-variant text-sm leading-relaxed">Unggah draf artikel ilmiah yang telah siap untuk proses review awal.</p>
                        <div class="bg-primary/5 mt-4 rounded-full p-3">
                            <span class="material-symbols-outlined text-primary">description</span>
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="bg-surface-container-lowest group relative flex flex-col items-center rounded-2xl p-8 text-center md:rounded-none md:bg-transparent">
                        <div class="border-primary font-headline text-primary z-10 mb-6 flex h-16 w-16 items-center justify-center rounded-full border-4 bg-white text-2xl font-bold shadow-lg transition-transform group-hover:scale-110">2</div>
                        <div class="bg-outline-variant/30 absolute left-1/2 top-16 hidden h-0.5 w-full md:block"></div>
                        <h3 class="font-headline mb-3 text-lg font-bold">Isi Form Request</h3>
                        <p class="text-on-surface-variant text-sm leading-relaxed">Lengkapi data administrasi dan detail jurnal tujuan pengiriman LOA.</p>
                        <div class="bg-primary/5 mt-4 rounded-full p-3">
                            <span class="material-symbols-outlined text-primary">edit_note</span>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="bg-surface-container-lowest group relative flex flex-col items-center rounded-2xl p-8 text-center md:rounded-none md:bg-transparent">
                        <div class="border-primary font-headline text-primary z-10 mb-6 flex h-16 w-16 items-center justify-center rounded-full border-4 bg-white text-2xl font-bold shadow-lg transition-transform group-hover:scale-110">3</div>
                        <div class="bg-outline-variant/30 absolute left-1/2 top-16 hidden h-0.5 w-full md:block"></div>
                        <h3 class="font-headline mb-3 text-lg font-bold">Verifikasi Admin</h3>
                        <p class="text-on-surface-variant text-sm leading-relaxed">Tim kurator kami akan memvalidasi kesesuaian dokumen dan status publikasi.</p>
                        <div class="bg-primary/5 mt-4 rounded-full p-3">
                            <span class="material-symbols-outlined text-primary">rule</span>
                        </div>
                    </div>
                    <!-- Step 4 -->
                    <div class="bg-surface-container-lowest group relative flex flex-col items-center rounded-2xl p-8 text-center md:rounded-none md:bg-transparent">
                        <div class="bg-tertiary shadow-tertiary/20 font-headline z-10 mb-6 flex h-16 w-16 items-center justify-center rounded-full text-2xl font-bold text-white shadow-xl transition-transform group-hover:scale-110">
                            <span class="material-symbols-outlined"
                                  style="font-variation-settings: 'FILL' 1;">task_alt</span>
                        </div>
                        <h3 class="font-headline mb-3 text-lg font-bold">Dapatkan LOA</h3>
                        <p class="text-on-surface-variant text-sm leading-relaxed">Unduh salinan resmi LOA digital Anda langsung dari dashboard.</p>
                        <div class="bg-tertiary/5 mt-4 rounded-full p-3">
                            <span class="material-symbols-outlined text-tertiary">download</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="mx-auto max-w-4xl px-8 py-24">
            <div class="mb-12">
                <h2 class="font-headline text-on-surface mb-2 text-4xl font-bold tracking-tight">Pertanyaan Populer</h2>
                <div class="editorial-gradient h-1.5 w-24 rounded-full"></div>
            </div>
            <div class="space-y-4">
                <!-- Accordion Item 1 -->
                <div class="bg-surface-container-lowest border-outline-variant/10 hover:border-primary/20 group overflow-hidden rounded-xl border shadow-[0_20px_40px_rgba(0,74,198,0.05)] transition-all">
                    <button class="flex w-full items-center justify-between px-8 py-6 text-left">
                        <span class="font-headline text-on-surface font-bold">Apa itu LOA dan mengapa saya membutuhkannya?</span>
                        <span class="material-symbols-outlined text-primary transition-transform group-hover:rotate-180">expand_more</span>
                    </button>
                    <div class="text-on-surface-variant px-8 pb-6 leading-relaxed">
                        LOA (Letter of Acceptance) adalah bukti tertulis bahwa artikel ilmiah Anda telah diterima untuk dipublikasikan oleh pihak jurnal atau konferensi. Ini penting untuk keperluan syarat kelulusan atau kenaikan pangkat akademik.
                    </div>
                </div>
                <!-- Accordion Item 2 -->
                <div class="bg-surface-container-lowest border-outline-variant/10 hover:border-primary/20 group overflow-hidden rounded-xl border shadow-[0_20px_40px_rgba(0,74,198,0.05)] transition-all">
                    <button class="flex w-full items-center justify-between px-8 py-6 text-left">
                        <span class="font-headline text-on-surface font-bold">Berapa lama proses verifikasi admin?</span>
                        <span class="material-symbols-outlined text-primary transition-transform group-hover:rotate-180">expand_more</span>
                    </button>
                    <div class="text-on-surface-variant px-8 pb-6 leading-relaxed">
                        Proses verifikasi memakan waktu 1-3 hari kerja tergantung pada volume antrean dan kelengkapan dokumen yang diunggah.
                    </div>
                </div>
                <!-- Accordion Item 3 -->
                <div class="bg-surface-container-lowest border-outline-variant/10 hover:border-primary/20 group overflow-hidden rounded-xl border shadow-[0_20px_40px_rgba(0,74,198,0.05)] transition-all">
                    <button class="flex w-full items-center justify-between px-8 py-6 text-left">
                        <span class="font-headline text-on-surface font-bold">Apakah ada biaya untuk pengajuan ini?</span>
                        <span class="material-symbols-outlined text-primary transition-transform group-hover:rotate-180">expand_more</span>
                    </button>
                    <div class="text-on-surface-variant px-8 pb-6 leading-relaxed">
                        
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA Banner -->
        <section class="mx-auto mb-20 max-w-7xl px-8">
            <div class="editorial-gradient relative overflow-hidden rounded-[2rem] p-12 text-center md:p-20">
                <div class="absolute inset-0 opacity-10">
                    <svg class="h-full w-full"
                         preserveAspectRatio="none"
                         viewBox="0 0 100 100">
                        <path d="M0 100 L100 0 L100 100 Z"
                              fill="white"></path>
                    </svg>
                </div>
                <div class="relative z-10 mx-auto max-w-3xl">
                    <h2 class="font-headline mb-6 text-4xl font-extrabold text-white md:text-5xl">Siap untuk Memvalidasi Prestasi Anda?</h2>
                    <p class="mb-10 text-lg text-white/80">Dapatkan Letter of Acceptance resmi dengan proses yang transparan dan profesional melalui LOA Curator.</p>
                    <a href="/register" class="text-primary font-headline hover:bg-surface-bright rounded-xl bg-white px-10 py-5 text-xl font-bold shadow-2xl transition-colors">
                        Mulai Pengajuan
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="mt-20 w-full rounded-t-[1.5rem] bg-slate-50 dark:bg-slate-900">
        <div class="mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-8 px-12 py-16 md:flex-row">
            <div class="flex flex-col gap-4 text-center md:text-left">
                <div class="font-headline text-lg font-black text-slate-900 dark:text-white">LOA Curator</div>
                <p class="font-body text-xs font-semibold uppercase tracking-widest text-slate-500">© 2024 LOA Curator. The Digital Curator Editorial Service.</p>
            </div>
            <div class="font-body flex flex-wrap justify-center gap-8 text-xs font-semibold uppercase tracking-widest">
                <a class="text-slate-500 underline-offset-4 transition-opacity hover:text-blue-700 hover:underline dark:hover:text-blue-300"
                   href="#">Privacy Policy</a>
                <a class="text-slate-500 underline-offset-4 transition-opacity hover:text-blue-700 hover:underline dark:hover:text-blue-300"
                   href="#">Terms of Service</a>
                <a class="text-slate-500 underline-offset-4 transition-opacity hover:text-blue-700 hover:underline dark:hover:text-blue-300"
                   href="#">Contact Support</a>
                <a class="text-slate-500 underline-offset-4 transition-opacity hover:text-blue-700 hover:underline dark:hover:text-blue-300"
                   href="#">University Partners</a>
            </div>
        </div>
    </footer>
</body>

</html>
