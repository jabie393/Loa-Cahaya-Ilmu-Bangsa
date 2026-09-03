<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pembayaran Submission #{{ $submission->id }} - Cahaya Ilmu Bangsa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        @keyframes pulse-slow { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }
        .animate-pulse-slow { animation: pulse-slow 2.5s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col justify-between"
      x-data="paymentApp({
          checkUrl: '{{ route('submissions.payment.check', $submission->id) }}',
          regenerateUrl: '{{ route('submissions.payment.regenerate', $submission->id) }}',
          initialStatus: '{{ $payment ? $payment->payment_status : ($submission->payment_status === 'paid' ? 'paid' : 'pending') }}',
          initialExpiresAt: '{{ $payment && $payment->expired_at ? $payment->expired_at->toIso8601String() : '' }}',
          isExtracting: {{ $isExtracting ? 'true' : 'false' }},
          initialQrisUrl: '{{ $payment ? $payment->qris_url : '' }}',
          initialQrString: '{{ $payment ? $payment->qr_string : '' }}',
          initialOrderId: '{{ $payment ? $payment->order_id : '' }}'
      })"
      x-init="initPayment()">

    <!-- Header / Navbar -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30 shadow-sm backdrop-blur-md bg-white/90">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-emerald-600 to-teal-700 flex items-center justify-center text-white font-black text-lg shadow-md shadow-emerald-600/20">
                    CIB
                </div>
                <div>
                    <h1 class="font-bold text-slate-900 text-sm sm:text-base leading-tight">Cahaya Ilmu Bangsa</h1>
                    <p class="text-[11px] text-slate-500 font-medium">Portal Publikasi & Submission LOA</p>
                </div>
            </div>
            <div>
                <a href="{{ route('filament.admin.resources.submissions.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-blue-700 bg-slate-100 hover:bg-slate-200/80 px-3 py-1.5 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    <span>Daftar Naskah</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8 w-full flex-grow">
        
        <!-- Breadcrumb & Title -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <span>Submission</span>
                <span>/</span>
                <span>ID #{{ $submission->id }}</span>
                <span>/</span>
                <span class="text-blue-700 font-semibold">Pembayaran</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Penyelesaian Pembayaran</h2>
                
                <!-- Status Badge -->
                <div class="flex items-center gap-2">
                    <template x-if="status === 'paid'">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">
                            <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                            Pembayaran Berhasil
                        </span>
                    </template>
                    <template x-if="status === 'pending' && !isExpired">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                            Menunggu Pembayaran
                        </span>
                    </template>
                    <template x-if="status === 'expired' || isExpired">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            QRIS Kedaluwarsa
                        </span>
                    </template>
                </div>
            </div>
        </div>

        <!-- Extracting / Loading State -->
        <template x-if="isExtracting">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-8 text-center shadow-sm">
                <div class="inline-flex p-4 bg-blue-50 text-blue-600 rounded-2xl mb-4 animate-bounce">
                    <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-1">Mengekstrak Metadata & Menghitung Harga...</h3>
                <p class="text-sm text-slate-500 max-w-md mx-auto mb-4">
                    Sistem sedang memindai judul, abstrak, dan jumlah penulis dari berkas PDF Anda secara otomatis. Halaman akan diperbarui otomatis dalam beberapa saat.
                </p>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden max-w-xs mx-auto">
                    <div class="bg-blue-600 h-full w-2/3 animate-pulse"></div>
                </div>
            </div>
        </template>

        <!-- Main Grid Layout -->
        <template x-if="!isExtracting">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Left Column: Order Summary & Article Details -->
                <div class="lg:col-span-7 space-y-6">
                    
                    <!-- Article Metadata Card -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <span>Detail Pengajuan Naskah</span>
                        </h3>

                        <div class="space-y-4 text-sm">
                            <div>
                                <span class="text-xs text-slate-500 block mb-0.5">Judul Artikel:</span>
                                <span class="font-bold text-slate-900 leading-snug block">
                                    {{ !empty($submission->title) ? $submission->title : 'Sedang diverifikasi...' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                                <div>
                                    <span class="text-xs text-slate-500 block mb-0.5">Jurnal Target:</span>
                                    <span class="font-semibold text-slate-800">{{ $submission->journal?->name ?? '-' }}</span>
                                    <span class="text-[11px] text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md inline-block mt-0.5 font-medium">
                                        {{ $submission->isExternal() ? 'Internasional (Scopus/Copernicus)' : 'Nasional Terindeks ISSN' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-500 block mb-0.5">Layanan DOI:</span>
                                    <span class="font-semibold {{ $submission->want_doi ? 'text-blue-700' : 'text-slate-700' }}">
                                        {{ $submission->want_doi ? 'Dengan DOI (Official)' : 'Tanpa DOI' }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                                <div>
                                    <span class="text-xs text-slate-500 block mb-0.5">Jumlah Penulis (Author):</span>
                                    <span class="font-bold text-slate-900">
                                        {{ $pricing ? $pricing['author_count'] : 1 }} Penulis
                                    </span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-500 block mb-0.5">Email Korespondensi:</span>
                                    <span class="font-medium text-slate-800 truncate block">{{ $submission->email }}</span>
                                </div>
                            </div>

                            @if(is_array($submission->authors) && count($submission->authors) > 0)
                            <div class="pt-2 border-t border-slate-100">
                                <span class="text-xs text-slate-500 block mb-1">Daftar Penulis Terdeteksi:</span>
                                <ul class="text-xs text-slate-700 space-y-1 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    @foreach($submission->authors as $index => $author)
                                        <li class="flex items-center gap-1.5">
                                            <span class="w-4 h-4 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-[10px] font-bold">{{ $index + 1 }}</span>
                                            <span class="font-medium">{{ is_array($author) ? ($author['name'] ?? '-') : $author }}</span>
                                            @if(is_array($author) && !empty($author['institution']))
                                                <span class="text-slate-400">({{ $author['institution'] }})</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Pricing Breakdown Card -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                            </svg>
                            <span>Rincian Biaya Publikasi</span>
                        </h3>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-center text-slate-600">
                                <span>Paket / Kategori:</span>
                                <span class="font-semibold text-slate-800">{{ $pricing['tier_name'] ?? 'Standar' }}</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600">
                                <span>Biaya Layanan:</span>
                                <span class="font-semibold text-slate-800">Rp {{ number_format($pricing['gross_amount'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600 text-xs">
                                <span>Biaya Transaksi (MDR QRIS):</span>
                                <span class="text-blue-700 font-semibold bg-blue-50 px-2 py-0.5 rounded">Gratis (Ditanggung Sistem)</span>
                            </div>
                            <div class="pt-3 border-t border-slate-200 flex justify-between items-baseline">
                                <span class="text-base font-bold text-slate-900">Total Pembayaran:</span>
                                <span class="text-2xl font-black text-blue-600">
                                    Rp {{ number_format($pricing['gross_amount'] ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: QRIS Display & Payment Actions -->
                <div class="lg:col-span-5">
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm sticky top-20 text-center">
                        
                        <!-- When Paid -->
                        <template x-if="status === 'paid'">
                            <div class="py-6 space-y-4">
                                <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto shadow-inner">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-black text-slate-900">Pembayaran Berhasil!</h3>
                                <p class="text-xs text-slate-500 max-w-xs mx-auto">
                                    Terima kasih! Pembayaran Anda telah terverifikasi secara otomatis oleh sistem. Naskah Anda kini masuk ke antrean Tim Reviewer.
                                </p>
                                <div class="bg-blue-50 border border-emerald-100 rounded-xl p-3 text-xs text-blue-800">
                                    Status LOA: <strong class="font-bold text-blue-700 uppercase tracking-wide">Approved (Disetujui)</strong>
                                </div>
                                <div class="pt-2">
                                    <a href="{{ route('filament.admin.resources.submissions.index') }}" class="w-full inline-flex items-center justify-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-4 rounded-xl text-sm transition-all shadow-md">
                                        <span>Kembali ke Dashboard</span>
                                    </a>
                                </div>
                            </div>
                        </template>

                        <!-- When Pending / Active -->
                        <template x-if="status === 'pending' && !isExpired">
                            <div>
                                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                                    <div class="flex items-center gap-1.5 text-xs text-slate-600 font-semibold">
                                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                                        <span>Scan QRIS</span>
                                    </div>
                                    
                                    <!-- Countdown Timer -->
                                    <div class="text-xs font-mono bg-amber-50 text-amber-800 border border-amber-200/80 px-2 py-0.5 rounded-lg flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <span x-text="countdownText">15:00</span>
                                    </div>
                                </div>

                                <!-- QRIS Image Box -->
                                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 mb-4 flex flex-col items-center justify-center relative">
                                    <template x-if="qrisUrl">
                                        <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
                                            <img :src="qrisUrl" alt="QRIS Midtrans" class="w-56 h-56 object-contain rounded-lg">
                                        </div>
                                    </template>
                                    <template x-if="!qrisUrl">
                                        <div class="w-56 h-56 flex flex-col items-center justify-center text-slate-400 bg-white rounded-xl border border-dashed border-slate-300">
                                            <svg class="w-8 h-8 animate-spin text-blue-600 mb-2" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span class="text-xs">Membuat QRIS...</span>
                                        </div>
                                    </template>

                                    <div class="mt-3 text-[11px] text-slate-500 font-medium">
                                        Mendukung GoPay, OVO, DANA, BCA, Mandiri & Seluruh M-Banking
                                    </div>

                                    @if(!config('services.midtrans.is_production', false))
                                    <div class="mt-3 w-full p-2.5 bg-amber-50/90 rounded-xl border border-amber-200 text-[11px] text-amber-900 text-left space-y-1.5 shadow-sm">
                                        <div class="font-bold flex items-center gap-1.5 text-amber-800">
                                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 1-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                                            </svg>
                                            <span>Petunjuk Simulasi Sandbox:</span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                                            <button type="button" @click="navigator.clipboard.writeText(qrisUrl); alert('URL QRIS disalin! Paste ke kolom simulator Midtrans.')"
                                                class="px-2.5 py-1 bg-white hover:bg-amber-100 text-amber-800 font-semibold rounded-md border border-amber-300 text-[10px] transition-colors">
                                                Salin URL
                                            </button>
                                            <a href="https://simulator.sandbox.midtrans.com/qris/index" target="_blank"
                                                class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-md text-[10px] transition-colors inline-flex items-center gap-1 ml-auto">
                                                <span>Buka Simulator</span>
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <!-- Order ID and Instructions -->
                                <div class="mb-4 text-left bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs">
                                    <div class="flex justify-between text-slate-500 mb-1">
                                        <span>Order ID:</span>
                                        <span class="font-mono text-slate-800 font-bold" x-text="orderId"></span>
                                    </div>
                                    <p class="text-[11px] text-slate-500">
                                        Pastikan nominal yang ditransfer sesuai dengan total tagihan untuk mempercepat verifikasi otomatis.
                                    </p>
                                </div>

                                <!-- Action Buttons -->
                                <div class="space-y-2">
                                    <button @click="checkStatus()"
                                            :disabled="isChecking"
                                            class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-bold py-3 px-4 rounded-xl text-xs sm:text-sm transition-all shadow-md shadow-emerald-600/20 active:scale-[0.99]">
                                        <svg x-show="isChecking" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg x-show="!isChecking" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                        </svg>
                                        <span x-text="isChecking ? 'Memeriksa...' : 'Periksa Status Pembayaran'"></span>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- When Expired -->
                        <template x-if="status === 'expired' || isExpired">
                            <div class="py-6 space-y-4">
                                <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto shadow-inner">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-black text-slate-900">QRIS Telah Kedaluwarsa</h3>
                                <p class="text-xs text-slate-500 max-w-xs mx-auto">
                                    Waktu pembayaran untuk QRIS ini telah habis. Silakan klik tombol di bawah untuk membuat QRIS baru.
                                </p>
                                <div class="pt-2">
                                    <button @click="regenerateQris()"
                                            :disabled="isRegenerating"
                                            class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold py-3 px-4 rounded-xl text-sm transition-all shadow-md active:scale-[0.99]">
                                        <svg x-show="isRegenerating" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span x-text="isRegenerating ? 'Membuat QRIS Baru...' : 'Buat QRIS Baru'"></span>
                                    </button>
                                </div>
                            </div>
                        </template>

                    </div>
                </div>

            </div>
        </template>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-400">
        <p>© 2026 Cahaya Ilmu Bangsa Institute. Sistem Pembayaran Otomatis didukung oleh Midtrans Payment Gateway.</p>
    </footer>

    <!-- AlpineJS Component Logic -->
    <script>
        function paymentApp(config) {
            return {
                checkUrl: config.checkUrl,
                regenerateUrl: config.regenerateUrl,
                status: config.initialStatus,
                expiresAt: config.initialExpiresAt ? new Date(config.initialExpiresAt) : null,
                isExtracting: config.isExtracting,
                qrisUrl: config.initialQrisUrl,
                qrString: config.initialQrString,
                orderId: config.initialOrderId,
                isExpired: false,
                isChecking: false,
                isRegenerating: false,
                countdownText: '15:00',
                pollTimer: null,
                countdownTimer: null,

                initPayment() {
                    // Update timer every second
                    this.updateCountdown();
                    this.countdownTimer = setInterval(() => this.updateCountdown(), 1000);

                    // Polling check every 5 seconds if pending or extracting
                    this.pollTimer = setInterval(() => {
                        if (this.status === 'pending' && !this.isExpired) {
                            this.checkStatus(true);
                        } else if (this.isExtracting) {
                            this.checkStatus(true);
                        }
                    }, 5000);
                },

                updateCountdown() {
                    if (!this.expiresAt || this.status !== 'pending') {
                        return;
                    }

                    const now = new Date();
                    const diff = this.expiresAt.getTime() - now.getTime();

                    if (diff <= 0) {
                        this.isExpired = true;
                        this.countdownText = '00:00';
                        return;
                    }

                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    this.countdownText = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                },

                async checkStatus(silent = false) {
                    if (!silent) this.isChecking = true;

                    try {
                        const res = await fetch(this.checkUrl, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const data = await res.json();

                        if (data.is_paid || data.status === 'paid') {
                            this.status = 'paid';
                            this.isExtracting = false;
                        } else if (data.status === 'expired' || data.is_expired) {
                            this.isExpired = true;
                            this.status = 'expired';
                        } else if (data.status === 'extracting') {
                            this.isExtracting = true;
                        } else {
                            // If it was extracting and now ready, reload to refresh metadata
                            if (this.isExtracting) {
                                window.location.reload();
                            }
                        }
                    } catch (e) {
                        console.error('Check status error:', e);
                    } finally {
                        if (!silent) this.isChecking = false;
                    }
                },

                async regenerateQris() {
                    this.isRegenerating = true;
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        const res = await fetch(this.regenerateUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const data = await res.json();
                        if (data.success) {
                            this.status = 'pending';
                            this.isExpired = false;
                            this.orderId = data.order_id;
                            this.qrisUrl = data.qris_url;
                            this.qrString = data.qr_string;
                            this.expiresAt = data.expired_at ? new Date(data.expired_at) : new Date(Date.now() + 15 * 60000);
                            this.updateCountdown();
                        } else {
                            alert(data.message || 'Gagal membuat QRIS baru.');
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan jaringan.');
                    } finally {
                        this.isRegenerating = false;
                    }
                }
            };
        }
    </script>
</body>
</html>
