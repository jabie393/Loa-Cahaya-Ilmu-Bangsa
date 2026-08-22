<div class="relative z-10 flex h-full min-h-[550px] w-full flex-col justify-between p-8 text-white md:p-12">
    <!-- Top Branding with Modern Glassmorphism Badge -->
    <div class="flex items-center space-x-4">
        <div class="relative group">
            <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-emerald-500 rounded-2xl blur opacity-40 group-hover:opacity-60 transition duration-300"></div>
            <div class="relative flex items-center justify-center p-3 bg-slate-900/70 backdrop-blur-xl border border-white/20 rounded-2xl shadow-xl">
                <img src="{{ asset('assets/logo.png') }}" alt="Cahaya Ilmu Bangsa Logo" class="h-12 w-auto object-contain drop-shadow-md">
            </div>
        </div>
        <div>
            <h2 class="text-xl font-bold tracking-tight text-white drop-shadow-md">Cahaya Ilmu Bangsa</h2>
            <p class="text-xs text-blue-200 font-medium drop-shadow">Portal Terpadu LOA & Repositori</p>
        </div>
    </div>

    <!-- Center Hero Feature Text -->
    <div class="my-auto max-w-lg py-8">
        <div class="inline-flex items-center space-x-2 px-3.5 py-1 mb-4 text-xs font-semibold tracking-wider text-emerald-300 uppercase bg-slate-900/60 rounded-full border border-emerald-400/30 backdrop-blur-md shadow-sm">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Single Sign-On System</span>
        </div>
        <h1 class="text-3xl font-extrabold leading-tight text-white drop-shadow-lg sm:text-4xl lg:text-5xl">
            Sistem Terpadu Publikasi & Repositori
        </h1>
        <p class="mt-4 text-sm sm:text-base text-slate-100/95 leading-relaxed drop-shadow-md">
            Akses satu pintu untuk pengajuan LOA, layanan penerbitan jurnal, dan pengarsipan repositori karya ilmiah Cahaya Ilmu Bangsa.
        </p>

        <!-- Feature Badges -->
        <div class="mt-6 flex flex-wrap gap-2">
            <span class="px-3 py-1 text-xs font-medium text-white bg-slate-900/50 backdrop-blur-md rounded-lg border border-white/20 shadow-sm">📄 Layanan LOA & Jurnal</span>
            <span class="px-3 py-1 text-xs font-medium text-white bg-slate-900/50 backdrop-blur-md rounded-lg border border-white/20 shadow-sm">📚 Repositori Digital</span>
            <span class="px-3 py-1 text-xs font-medium text-white bg-slate-900/50 backdrop-blur-md rounded-lg border border-white/20 shadow-sm">🔐 Akses Terintegrasi</span>
        </div>
    </div>

    <!-- Footer Note -->
    <div class="flex items-center justify-between text-xs text-slate-200/90 pt-4 border-t border-white/20 drop-shadow">
        <span>&copy; {{ date('Y') }} Cahaya Ilmu Bangsa</span>
    </div>
</div>

<!-- Background Image with Dark Overlay -->
<div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ filament('filament-auth-ui-enhancer')->getEmptyPanelBackgroundImageUrl() }}');">
    <div class="auth-hero-gradient absolute inset-0"></div>
</div>
