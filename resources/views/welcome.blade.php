<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" class="scroll-pt-24 scroll-smooth">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="{{ asset('favicon.ico') }}">
        <title>LOA | Panduan Pengajuan & Verifikasi</title>

        <!-- Primary Meta Tags -->
        <meta name="title" content="LOA | Panduan Pengajuan & Verifikasi">
        <meta name="description" content="Sistem kurasi digital resmi untuk mempermudah perolehan Letter of Acceptance (LOA) bagi civitas akademika dengan standar jurnal bereputasi.">

        <!-- Open Graph / Facebook / WhatsApp -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="LOA | Panduan Pengajuan & Verifikasi">
        <meta property="og:description" content="Sistem kurasi digital resmi untuk mempermudah perolehan Letter of Acceptance (LOA) bagi civitas akademika dengan standar jurnal bereputasi.">
        <meta property="og:image" content="{{ asset('assets/bg.png') }}">

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image">
        <meta property="twitter:url" content="{{ url()->current() }}">
        <meta property="twitter:title" content="LOA | Panduan Pengajuan & Verifikasi">
        <meta property="twitter:description" content="Sistem kurasi digital resmi untuk mempermudah perolehan Letter of Acceptance (LOA) bagi civitas akademika dengan standar jurnal bereputasi.">
        <meta property="twitter:image" content="{{ asset('assets/bg.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
            rel="stylesheet">


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

            @keyframes fadeInScale {
                from {
                    opacity: 0;
                    transform: scale(0.98) translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }

            .animate-reveal {
                animation: fadeInScale 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
            }

            #pdf-canvas {
                object-fit: cover;
            }
        </style>

        <!-- Styles / Scripts -->
        <link rel="stylesheet" href="{{ asset('css/mascot.css') }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-body text-on-surface selection:bg-primary/20 bg-surface flex h-[100dvh] min-h-screen flex-col justify-between overflow-hidden antialiased">
        <!-- TopNavBar -->
        <nav class="fixed top-0 z-50 w-full bg-white/20 shadow-[0_20px_40px_rgba(0,74,198,0.05)] backdrop-blur-xl dark:bg-slate-950/80">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-8 py-4">
                <div class="font-headline text-xl font-bold tracking-tighter text-slate-900 dark:text-white">
                    <a href="{{ url('/') }}" class="flex items-center gap-2">
                        <img src="https://aset.warunayama.org/images/logo.png" alt="" class="h-8 w-8">
                        <span>LOA</span>
                    </a>
                </div>

                <div class="flex items-center gap-4">
                    @if (Route::has('login'))
                        <div class="flex items-center gap-4">
                            <!-- Auth Container -->
                            <div id="sso-auth-container" style="display: {{ Auth::check() ? 'block' : 'none' }};">
                                <a href="/journal"
                                    class="bg-primary text-on-primary shadow-primary/20 font-headline scale-95 rounded-xl px-5 py-2 text-xs font-semibold shadow-lg transition-transform active:scale-90 sm:px-6 sm:py-2.5 sm:text-sm">
                                    Dashboard
                                </a>
                            </div>

                            <!-- Guest Container -->
                            <div id="sso-guest-container" class="flex items-center gap-3 sm:gap-4" style="display: {{ Auth::check() ? 'none' : 'flex' }};">
                                <a href="/login" class="font-headline hover:text-primary text-xs font-bold text-slate-600 sm:text-sm dark:text-slate-400">
                                    Log in
                                </a>

                                @if (Route::has('register'))
                                    <a href="/register"
                                        class="bg-primary text-on-primary shadow-primary/20 font-headline scale-95 rounded-xl px-5 py-2 text-xs font-semibold shadow-lg transition-transform active:scale-90 sm:px-6 sm:py-2.5 sm:text-sm">
                                        Register
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </nav>

        <main class="relative flex w-full flex-1 flex-col justify-center overflow-hidden">
            <!-- Hero Section -->
            <section class="relative flex h-full w-full items-center overflow-hidden pt-16 sm:pt-20 lg:pt-0">
                <!-- Right Side Building Artwork for large screens (flushed to right edge) -->
                <div class="pointer-events-none absolute bottom-0 right-0 top-0 z-0 hidden w-[50%] select-none items-center justify-end overflow-hidden lg:flex xl:w-[52%] 2xl:w-[50%]">
                    <div class="relative flex h-full w-full items-center justify-end">
                        <img src="{{ asset('assets/bg.png') }}" alt="Building Architecture" class="h-full w-full object-cover object-right-bottom" />

                        <!-- Soft bottom fade to dissolve street into ground -->
                        <div class="from-surface pointer-events-none absolute inset-x-0 bottom-0 h-36 bg-gradient-to-t via-white/80 to-transparent"></div>

                        <!-- Soft left fade matching left bg color to blend seamlessly -->
                        <div class="from-surface pointer-events-none absolute inset-y-0 left-0 w-36 bg-gradient-to-r via-[#edf4fa]/60 to-transparent"></div>
                    </div>
                </div>

                <!-- Mobile / Tablet Full-Cover Background Image -->
                <div class="pointer-events-none absolute inset-0 z-0 select-none overflow-hidden lg:hidden">
                    <img src="{{ asset('assets/bg.png') }}" alt="Building Architecture" class="h-full w-full object-cover object-center" />
                    <!-- Soft overlay for text legibility -->
                    <div class="bg-surface/10 pointer-events-none absolute inset-0 backdrop-blur-[2px]"></div>
                    <div class="from-surface via-surface/60 to-surface/40 pointer-events-none absolute inset-0 bg-gradient-to-t"></div>
                </div>

                <div class="relative z-10 mx-auto my-auto w-full max-w-7xl px-6 py-4 sm:px-8 sm:py-8">
                    <div class="grid grid-cols-1 items-center gap-8 lg:grid-cols-12">
                        <!-- Left Column (Full width on mobile): Typography & CTAs -->
                        <div class="col-span-1 flex flex-col justify-center text-left lg:col-span-7 xl:col-span-6">
                            <div
                                class="border-primary/20 mb-3 inline-flex items-center gap-2 self-start rounded-full border bg-white/80 px-3.5 py-1 shadow-sm backdrop-blur-sm sm:mb-5 sm:px-4 sm:py-1.5">
                                <span class="bg-primary flex h-2 w-2 rounded-full"></span>
                                <span class="font-headline text-primary text-[10px] font-bold uppercase tracking-widest sm:text-xs">Portal
                                    Terpadu LOA &amp; Repositori</span>
                            </div>

                            <h1 class="font-headline mb-3 text-3xl font-extrabold leading-[1.15] tracking-tighter text-slate-900 sm:mb-5 sm:text-5xl md:text-6xl lg:text-7xl">
                                Sistem Terpadu <br />
                                <span class="bg-linear-to-r from-blue-700 to-blue-500 bg-clip-text text-transparent">Publikasi
                                    &amp; Repositori</span>
                            </h1>

                            <p class="mb-5 max-w-xl text-sm font-medium leading-relaxed text-slate-700 sm:mb-8 sm:text-base md:text-lg lg:text-xl">
                                Akses satu pintu untuk pengajuan <strong>Letter of Acceptance (LOA)</strong>, layanan penerbitan jurnal, dan pengarsipan repositori karya ilmiah Cahaya Ilmu Bangsa.
                            </p>

                            <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:gap-4">
                                <a href="{{ auth()->check() ? '/journal' : '/register' }}"
                                    class="editorial-gradient font-headline shadow-primary/30 group flex items-center justify-center gap-2.5 rounded-xl px-6 py-3 text-sm font-bold text-white transition-all hover:scale-105 hover:shadow-xl sm:gap-3 sm:rounded-2xl sm:px-8 sm:py-4 sm:text-lg">
                                    Ajukan LOA Sekarang
                                    <span class="material-symbols-outlined text-lg transition-transform group-hover:translate-x-1 sm:text-2xl">arrow_forward</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>


        </main>

        <!-- Mascot Helper -->
        <x-mascot />

        <!-- Footer -->
        <footer class="relative z-10 w-full flex-none border-t border-slate-200/50 bg-white/40 px-6 py-3 backdrop-blur-md sm:px-12 sm:py-4 dark:bg-slate-900/40">
            <div class="mx-auto flex w-full max-w-7xl flex-row items-center justify-between text-xs font-semibold uppercase tracking-widest text-slate-500">
                <div class="flex items-center gap-2">
                    <span class="font-headline text-sm font-black text-slate-900 dark:text-white">LOA</span>
                    <span class="hidden sm:inline">|</span>
                    <span class="hidden sm:inline">© 2026 Cahaya Ilmu Bangsa.</span>
                </div>
                <div>Developed by <a href="https://instagram.com/ryudevs" class="hover:text-primary transition-colors hover:underline">RyuDevs</a></div>
            </div>
        </footer>
        <script src="{{ asset('js/mascot.js') }}" defer></script>
    </body>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        // Set up PDF.js worker
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        // Render PDF cover
        async function renderPDFCover() {
            try {
                const pdf = await pdfjsLib.getDocument('/assets/panduan.pdf').promise;
                const firstPage = await pdf.getPage(1);

                const canvas = document.getElementById('pdf-canvas');
                const context = canvas.getContext('2d');

                // Set canvas size to match PDF page
                const viewport = firstPage.getViewport({
                    scale: 1.5
                });
                canvas.width = viewport.width;
                canvas.height = viewport.height;

                // Render page to canvas
                await firstPage.render({
                    canvasContext: context,
                    viewport: viewport
                }).promise;
            } catch (error) {
                console.error('Error rendering PDF:', error);
                // Fallback: show icon if PDF fails to load
                const canvas = document.getElementById('pdf-canvas');
                const context = canvas.getContext('2d');
                canvas.width = 280;
                canvas.height = 400;
                context.fillStyle = '#f5f5f5';
                context.fillRect(0, 0, 280, 400);
            }
        }

        // Render when document is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', renderPDFCover);
        } else {
            renderPDFCover();
        }
    </script>


    <!-- SSO Iframe Check & Dynamic Auth Synchronization -->
    <iframe id="sso-iframe" src="{{ config('services.repo_url', env('REPO_URL', 'http://127.0.0.1:8001')) }}/sso/iframe-check?origin={{ urlencode(url('/')) }}" style="display:none;"></iframe>

    <script>
        (function() {
            let localUserLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
            const repoUrl = "{{ config('services.repo_url', env('REPO_URL', 'http://127.0.0.1:8001')) }}";

            window.addEventListener('message', function(event) {
                if (!event.origin.startsWith(repoUrl)) return;

                if (event.data && event.data.type === 'cib_sso_status') {
                    const sso = event.data.data;

                    const authContainer = document.getElementById('sso-auth-container');
                    const guestContainer = document.getElementById('sso-guest-container');

                    if (sso.logged_in && !localUserLoggedIn) {
                        localUserLoggedIn = true;

                        // Dynamically update UI immediately
                        if (authContainer) authContainer.style.display = 'block';
                        if (guestContainer) guestContainer.style.display = 'none';

                        // Silent Auto-Login via AJAX
                        fetch('/sso/callback-ajax', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(sso)
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (!data.success) {
                                    localUserLoggedIn = false;
                                    if (authContainer) authContainer.style.display = 'none';
                                    if (guestContainer) guestContainer.style.display = 'flex';
                                }
                            })
                            .catch(() => {
                                localUserLoggedIn = false;
                                if (authContainer) authContainer.style.display = 'none';
                                if (guestContainer) guestContainer.style.display = 'flex';
                            });
                    }
                }
            });

            // Helper to reload iframe check
            function checkSso() {
                const iframe = document.getElementById('sso-iframe');
                if (iframe) {
                    iframe.src = iframe.src;
                }
            }

            // Check on tab focus/switch
            window.addEventListener('focus', checkSso);

            // Check periodically in background every 15 seconds
            setInterval(checkSso, 15000);

            // Poll local session status to detect logout dynamically
            function checkLocalSession() {
                fetch('/sso/local-check', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.logged_in && localUserLoggedIn) {
                            localUserLoggedIn = false;
                            const authContainer = document.getElementById('sso-auth-container');
                            const guestContainer = document.getElementById('sso-guest-container');
                            if (authContainer) authContainer.style.display = 'none';
                            if (guestContainer) guestContainer.style.display = 'flex';
                        }
                    })
                    .catch(() => {});
            }

            window.addEventListener('focus', checkLocalSession);
            setInterval(checkLocalSession, 10000); // Check local session every 10 seconds
        })();
    </script>

</html>
