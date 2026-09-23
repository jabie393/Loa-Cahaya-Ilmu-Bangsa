<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" class="scroll-pt-24 scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <title>LOA | Panduan Pengajuan & Verifikasi</title>

    <!-- Primary Meta Tags -->
    <meta name="title" content="LOA | Panduan Pengajuan & Verifikasi">
    <meta name="description"
        content="Sistem kurasi digital resmi untuk mempermudah perolehan Letter of Acceptance (LOA) bagi civitas akademika dengan standar jurnal bereputasi.">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="LOA | Panduan Pengajuan & Verifikasi">
    <meta property="og:description"
        content="Sistem kurasi digital resmi untuk mempermudah perolehan Letter of Acceptance (LOA) bagi civitas akademika dengan standar jurnal bereputasi.">
    <meta property="og:image" content="{{ asset('assets/bg.png') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="LOA | Panduan Pengajuan & Verifikasi">
    <meta property="twitter:description"
        content="Sistem kurasi digital resmi untuk mempermudah perolehan Letter of Acceptance (LOA) bagi civitas akademika dengan standar jurnal bereputasi.">
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

        /* Page Opening Transitions */
        @keyframes navFadeDown {
            0% {
                opacity: 0;
                transform: translateY(-16px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes heroFadeUp {
            0% {
                opacity: 0;
                transform: translateY(28px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes imageEntrance {
            0% {
                opacity: 0;
                transform: scale(1.04) translateX(12px);
            }

            100% {
                opacity: 1;
                transform: scale(1) translateX(0);
            }
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }

        .anim-nav {
            animation: navFadeDown 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .anim-badge {
            animation: heroFadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both;
        }

        .anim-title {
            animation: heroFadeUp 0.85s cubic-bezier(0.16, 1, 0.3, 1) 0.2s both;
        }

        .anim-desc {
            animation: heroFadeUp 0.85s cubic-bezier(0.16, 1, 0.3, 1) 0.32s both;
        }

        .anim-cta {
            animation: heroFadeUp 0.9s cubic-bezier(0.16, 1, 0.3, 1) 0.44s both;
        }

        .anim-image {
            animation: imageEntrance 1.2s cubic-bezier(0.16, 1, 0.3, 1) 0.15s both;
        }

        .anim-footer {
            animation: fadeIn 0.9s ease-out 0.5s both;
        }
    </style>

    <!-- Styles / Scripts -->
    <link rel="stylesheet" href="{{ asset('css/mascot.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="font-body text-on-surface selection:bg-primary/20 bg-surface flex h-screen h-[100dvh] max-h-screen flex-col justify-between overflow-hidden antialiased">
    <!-- TopNavBar (Flex-none: naturally placed above main with zero overlap) -->
    <nav
        class="anim-nav relative z-50 w-full flex-none border-b border-slate-200/70 bg-white/80 shadow-[0_4px_24px_rgba(0,74,198,0.06)] backdrop-blur-xl dark:border-slate-800/70 dark:bg-slate-950/80 transition-all">
        <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-3 sm:px-8 sm:py-3.5">
            <div class="font-headline text-xl font-bold tracking-tighter text-slate-900 dark:text-white">
                <a href="{{ url('/') }}"
                    class="flex items-center gap-2.5 transition-transform hover:scale-105 active:scale-95">
                    <img src="https://aset.warunayama.org/images/logo.png" alt="" class="h-8 w-8">
                    <span class="tracking-tight">LOA</span>
                </a>
            </div>

            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                    @php
                        $dashboardUrl = Auth::check() && Auth::user()->hasAnyRole(['ryu_dev'])
                            ? '/dev-payouts'
                            : '/journal';
                    @endphp
                    <div class="flex items-center gap-3 sm:gap-4">
                        <!-- Auth Container -->
                        <div id="sso-auth-container" style="display: {{ Auth::check() ? 'block' : 'none' }};">
                            <a id="sso-dashboard-btn" href="{{ $dashboardUrl }}"
                                class="font-headline inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2 sm:px-6 sm:py-2 text-xs sm:text-sm font-bold text-white shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30 hover:-translate-y-0.5 active:translate-y-0 transition-all">
                                Dashboard
                            </a>
                        </div>

                        <!-- Guest Container -->
                        <div id="sso-guest-container" class="flex items-center gap-3 sm:gap-4"
                            style="display: {{ Auth::check() ? 'none' : 'flex' }};">
                            <a href="/login"
                                class="font-headline text-xs font-bold text-slate-600 hover:text-blue-600 sm:text-sm dark:text-slate-400 dark:hover:text-blue-400 transition-colors">
                                Log in
                            </a>

                            @if (Route::has('register'))
                                <a href="/register"
                                    class="font-headline inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2 sm:px-6 sm:py-2 text-xs sm:text-sm font-bold text-white shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30 hover:-translate-y-0.5 active:translate-y-0 transition-all">
                                    Register
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- Main Content (Takes exactly 100% of remaining height between nav and footer) -->
    <main class="relative flex w-full flex-1 min-h-0 flex-col justify-center overflow-hidden">
        <!-- Hero Section -->
        <section class="relative flex h-full w-full items-center overflow-hidden py-4 sm:py-6">
            <!-- Right Side Building Artwork for large screens (flushed to right edge) -->
            <div
                class="anim-image pointer-events-none absolute bottom-0 right-0 top-0 z-0 hidden w-[50%] select-none items-center justify-end overflow-hidden lg:flex xl:w-[52%] 2xl:w-[50%]">
                <div class="relative flex h-full w-full items-center justify-end">
                    <img src="{{ asset('assets/bg.png') }}" alt="Building Architecture"
                        class="h-full w-full object-cover object-right-bottom" />

                    <!-- Soft bottom fade to dissolve street into ground -->
                    <div
                        class="from-surface pointer-events-none absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t via-white/80 to-transparent">
                    </div>

                    <!-- Soft left fade matching left bg color to blend seamlessly -->
                    <div
                        class="from-surface pointer-events-none absolute inset-y-0 left-0 w-48 xl:w-64 bg-gradient-to-r via-[#edf4fa]/70 to-transparent">
                    </div>
                </div>
            </div>

            <!-- Mobile / Tablet Full-Cover Background Image -->
            <div class="anim-image pointer-events-none absolute inset-0 z-0 select-none overflow-hidden lg:hidden">
                <img src="{{ asset('assets/bg.png') }}" alt="Building Architecture"
                    class="h-full w-full object-cover object-center" />
                <!-- Soft overlay for text legibility -->
                <div class="bg-surface/10 pointer-events-none absolute inset-0 backdrop-blur-[2px]"></div>
                <div
                    class="from-surface via-surface/60 to-surface/40 pointer-events-none absolute inset-0 bg-gradient-to-t">
                </div>
            </div>

            <div class="relative z-10 mx-auto my-auto w-full max-w-7xl px-6 sm:px-8">
                <div class="grid grid-cols-1 items-center gap-6 lg:grid-cols-12 lg:gap-8">
                    <!-- Left Column (Full width on mobile): Typography & CTAs -->
                    <div class="col-span-1 flex flex-col justify-center text-left lg:col-span-7 xl:col-span-6">
                        <!-- Badge -->
                        <div
                            class="anim-badge mb-2.5 sm:mb-3.5 lg:mb-4 inline-flex items-center gap-2 self-start rounded-full border border-blue-200/90 bg-white/90 px-3.5 py-1.5 shadow-xs backdrop-blur-md dark:border-blue-900/60 dark:bg-slate-900/80">
                            <span class="relative flex h-2 w-2">
                                <span
                                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-blue-400 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-blue-600"></span>
                            </span>
                            <span
                                class="font-headline text-[11px] sm:text-xs font-bold uppercase tracking-wider text-blue-700 dark:text-blue-300">
                                Portal Terpadu LOA &amp; Repositori
                            </span>
                        </div>

                        <!-- Main Headline -->
                        <h1
                            class="anim-title font-headline mb-3 text-3xl font-extrabold leading-[1.12] tracking-tight text-slate-900 sm:mb-3.5 sm:text-4xl md:text-5xl lg:text-[3.15rem] xl:text-[3.5rem] dark:text-white">
                            Sistem Terpadu <br />
                            <span
                                class="bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-600 bg-clip-text text-transparent">
                                Publikasi &amp; Repositori
                            </span>
                        </h1>

                        <!-- Subheading / Description -->
                        <p
                            class="anim-desc mb-5 max-w-lg text-sm sm:text-base font-normal leading-relaxed text-slate-600 dark:text-slate-300">
                            Akses satu pintu untuk pengajuan <strong
                                class="font-semibold text-slate-900 dark:text-white">Letter of Acceptance
                                (LOA)</strong>, layanan penerbitan jurnal, dan pengarsipan repositori karya ilmiah
                            Cahaya Ilmu Bangsa.
                        </p>

                        <!-- CTA Button Container (Refined, stylish, proportional) -->
                        <div class="anim-cta flex flex-wrap items-center gap-3.5 sm:gap-4">
                            <a href="{{ auth()->check() ? ($dashboardUrl ?? '/journal') : '/login' }}"
                                class="editorial-gradient font-headline group inline-flex w-full sm:w-auto items-center justify-center gap-2.5 rounded-xl px-6 py-3 sm:px-7 sm:py-3 text-sm sm:text-base font-bold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-blue-500/35 active:translate-y-0">
                                <span>Ajukan LOA Sekarang</span>
                                <span
                                    class="material-symbols-outlined text-lg sm:text-xl transition-transform duration-200 group-hover:translate-x-1">arrow_forward</span>
                            </a>

                            <div
                                class="hidden sm:flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400">
                                <span
                                    class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                    <span class="material-symbols-outlined text-sm font-bold">check</span>
                                </span>
                                <span>Layanan Resmi &amp; Terverifikasi</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Mascot Helper -->
    <x-mascot />

    <!-- Footer (Flex-none: neatly pinned to the bottom) -->
    <footer
        class="anim-footer relative z-10 w-full flex-none border-t border-slate-200/50 bg-white/50 px-6 py-2.5 sm:px-12 sm:py-3 backdrop-blur-md dark:bg-slate-900/50">
        <div
            class="mx-auto flex w-full max-w-7xl flex-row items-center justify-between text-xs font-semibold uppercase tracking-widest text-slate-500">
            <div class="flex items-center gap-2">
                <span class="font-headline text-sm font-black text-slate-900 dark:text-white">LOA</span>
                <span class="hidden sm:inline">|</span>
                <span class="hidden sm:inline">© 2026 Cahaya Ilmu Bangsa.</span>
            </div>
            <div>Developed by <a href="https://ryudevs.id" target="_blank" rel="noopener noreferrer"
                    class="hover:text-primary transition-colors hover:underline">RyuDevs</a></div>
        </div>
    </footer>
    <script src="{{ asset('js/mascot.js') }}?v={{ file_exists(public_path('js/mascot.js')) ? filemtime(public_path('js/mascot.js')) : time() }}" defer></script>
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
<iframe id="sso-iframe"
    src="{{ config('services.repo_url', env('REPO_URL', 'http://127.0.0.1:8001')) }}/sso/iframe-check?origin={{ urlencode(url('/')) }}"
    style="display:none;"></iframe>

<script>
    (function () {
        let localUserLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
        const repoUrl = "{{ config('services.repo_url', env('REPO_URL', 'http://127.0.0.1:8001')) }}";

        window.addEventListener('message', function (event) {
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
                            } else if (data.redirect_url) {
                                const dashboardBtn = document.getElementById('sso-dashboard-btn');
                                if (dashboardBtn) {
                                    dashboardBtn.href = data.redirect_url;
                                }
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
                    } else if (data.logged_in && data.redirect_url) {
                        const dashboardBtn = document.getElementById('sso-dashboard-btn');
                        if (dashboardBtn) {
                            dashboardBtn.href = data.redirect_url;
                        }
                    }
                })
                .catch(() => { });
        }

        window.addEventListener('focus', checkLocalSession);
        setInterval(checkLocalSession, 10000); // Check local session every 10 seconds
    })();
</script>

</html>