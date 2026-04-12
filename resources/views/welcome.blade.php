<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-theme="light"
      class="scroll-pt-24 scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <title>LOA | Panduan Pengajuan & Verifikasi</title>

    <!-- Fonts -->
    <link rel="preconnect"
          href="https://fonts.googleapis.com">
    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
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
    </style>

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-surface font-body text-on-surface selection:bg-primary/20 antialiased">
    <!-- TopNavBar -->
    <nav class="fixed top-0 z-50 w-full bg-white/20 shadow-[0_20px_40px_rgba(0,74,198,0.05)] backdrop-blur-xl dark:bg-slate-950/80">
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
                   href="#guide">LOA Guide</a>
            </div>
            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                    <div class="flex items-center gap-4">
                        @auth
                            <a href="/journal"
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
        <section class="relative bg-cover bg-fixed bg-bottom bg-no-repeat" style="background-image: url('{{ asset('assets/bg.jpg') }}');">
            <!-- Overlay for better contrast -->
            <div class="absolute inset-0 bg-white/30 backdrop-blur-[2px]"></div>
            
            <div class="relative mx-auto flex w-full max-w-7xl flex-col items-center justify-center px-8 py-32 text-center md:py-48 lg:w-3/5">
                <div class="mb-6 flex items-center gap-2 rounded-full border border-primary/20 bg-white/80 px-4 py-1.5 shadow-sm">
                    <span class="flex h-2 w-2 rounded-full bg-primary"></span>
                    <span class="font-headline text-primary text-xs font-bold uppercase tracking-widest">Service Editorial Profesional</span>
                </div>
                
                <h1 class="font-headline text-slate-900 mb-6 text-5xl font-extrabold leading-tight tracking-tighter md:text-7xl">
                    Panduan Pengajuan <br /> 
                    <span class="bg-linear-to-r from-blue-700 to-blue-500 bg-clip-text text-transparent">& Verifikasi</span>
                </h1>
                
                <p class="text-slate-700 mb-10 max-w-xl text-lg font-medium leading-relaxed md:text-xl">
                    Sistem kurasi digital resmi untuk mempermudah perolehan <strong>Letter of Acceptance (LOA)</strong> bagi civitas akademika dengan standar jurnal bereputasi.
                </p>
                
                <div class="flex flex-col items-center gap-4 sm:flex-row">
                    <a href="{{ auth()->check() ? '/journal' : '/register' }}"
                       class="editorial-gradient font-headline shadow-primary/30 group flex items-center gap-3 rounded-2xl px-8 py-4 text-lg font-bold text-white transition-all hover:scale-105 hover:shadow-xl">
                        Ajukan LOA Sekarang
                        <span class="material-symbols-outlined transition-transform group-hover:translate-x-1">arrow_forward</span>
                    </a>
                    
                    <a href="#guide"
                       class="font-headline text-slate-900 hover:bg-white/50 rounded-2xl border border-slate-200 bg-white/50 px-8 py-4 text-lg font-bold backdrop-blur-sm transition-all hover:scale-105 hover:shadow-xl">
                        Pelajari Alur
                    </a>
                </div>
                
                <!-- Scroll Indicator -->
                <div class="mt-20 animate-bounce opacity-50">
                    <span class="material-symbols-outlined text-3xl">expand_more</span>
                </div>
            </div>
        </section>

        <!-- Alur Pengajuan LOA (Process Flow) -->
        <section class="bg-surface-container-low/50 animate-reveal py-24"
                 id="guide">
            <div class="mx-auto max-w-7xl px-8">
                <div class="mx-auto mb-16 max-w-2xl text-center">
                    <h2 class="font-headline text-on-surface mb-4 text-4xl font-bold tracking-tight">Alur Proses Kurasi</h2>
                    <p class="text-on-surface-variant">Ikuti langkah-langkah terukur untuk mendapatkan sertifikasi resmi dari tim editorial kami.</p>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4 md:gap-0">
                    <!-- Step 1 -->
                    <div class="bg-surface-container-lowest group relative flex flex-col items-center rounded-2xl p-8 text-center md:rounded-none md:bg-transparent">
                        <div class="editorial-gradient shadow-primary/20 font-headline z-10 mb-6 flex h-16 w-16 items-center justify-center rounded-full text-2xl font-bold text-white shadow-xl transition-transform group-hover:scale-110">1</div>
                        <div class="bg-outline-variant/30 absolute left-1/2 top-16 hidden h-0.5 w-full md:block"></div>
                        <h3 class="font-headline mb-3 text-lg font-bold">Registrasi Akun</h3>
                        <p class="text-on-surface-variant text-sm leading-relaxed">Buat akun untuk masuk ke dashboard digital dan mengelola profil peneliti Anda.</p>
                        <div class="bg-primary/5 mt-4 rounded-full p-3">
                            <span class="material-symbols-outlined text-primary">person_add</span>
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="bg-surface-container-lowest group relative flex flex-col items-center rounded-2xl p-8 text-center md:rounded-none md:bg-transparent">
                        <div class="border-primary font-headline text-primary z-10 mb-6 flex h-16 w-16 items-center justify-center rounded-full border-4 bg-white text-2xl font-bold shadow-lg transition-transform group-hover:scale-110">2</div>
                        <div class="bg-outline-variant/30 absolute left-1/2 top-16 hidden h-0.5 w-full md:block"></div>
                        <h3 class="font-headline mb-3 text-lg font-bold">Kirim Pengajuan</h3>
                        <p class="text-on-surface-variant text-sm leading-relaxed">Lengkapi detail naskah dan lampirkan bukti pembayaran dalam satu form praktis.</p>
                        <div class="bg-primary/5 mt-4 rounded-full p-3">
                            <span class="material-symbols-outlined text-primary">post_add</span>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="bg-surface-container-lowest group relative flex flex-col items-center rounded-2xl p-8 text-center md:rounded-none md:bg-transparent">
                        <div class="border-primary font-headline text-primary z-10 mb-6 flex h-16 w-16 items-center justify-center rounded-full border-4 bg-white text-2xl font-bold shadow-lg transition-transform group-hover:scale-110">3</div>
                        <div class="bg-outline-variant/30 absolute left-1/2 top-16 hidden h-0.5 w-full md:block"></div>
                        <h3 class="font-headline mb-3 text-lg font-bold">Proses Verifikasi</h3>
                        <p class="text-on-surface-variant text-sm leading-relaxed">Tim ahli kami melakukan kurasi naskah dan validasi administrasi secara profesional.</p>
                        <div class="bg-primary/5 mt-4 rounded-full p-3">
                            <span class="material-symbols-outlined text-primary">verified_user</span>
                        </div>
                    </div>
                    <!-- Step 4 -->
                    <div class="bg-surface-container-lowest group relative flex flex-col items-center rounded-2xl p-8 text-center md:rounded-none md:bg-transparent">
                        <div class="bg-tertiary shadow-tertiary/20 font-headline z-10 mb-6 flex h-16 w-16 items-center justify-center rounded-full text-2xl font-bold text-white shadow-xl transition-transform group-hover:scale-110">
                            <span class="material-symbols-outlined"
                                  style="font-variation-settings: 'FILL' 1;">workspace_premium</span>
                        </div>
                        <h3 class="font-headline mb-3 text-lg font-bold">Paket Sertifikat</h3>
                        <p class="text-on-surface-variant text-sm leading-relaxed">Terima paket lengkap: LOA, Author Certificate, dan Plagiarism-Free Certificate.</p>
                        <div class="bg-tertiary/5 mt-4 rounded-full p-3">
                            <span class="material-symbols-outlined text-tertiary">download_for_offline</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        
    </main>

    <!-- Footer -->
    <footer class="mt-20 w-full rounded-t-[1.5rem] bg-slate-50 dark:bg-slate-900">
        <div class="mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-8 px-12 py-16 md:items-end md:flex-row">
            <div class="flex flex-col gap-4 text-center md:text-left">
                <div class="font-headline text-lg font-black text-slate-900 dark:text-white">LOA</div>
                <p class="font-body text-xs font-semibold uppercase tracking-widest text-slate-500">© 2026 Cahaya Ilmu Bangsa.</p>
            </div>
            <div class="flex flex-col gap-4 text-center md:text-right">
                <p class="font-body text-xs font-semibold uppercase tracking-widest text-slate-500">Developed by <a href="https://instagram.com/ryudevs">RyuDevs</a></p>
            </div>
        </div>
    </footer>
</body>

</html>
