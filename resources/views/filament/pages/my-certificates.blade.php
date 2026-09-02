<x-filament-panels::page>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        .font-manrope {
            font-family: 'Manrope', sans-serif;
        }

        .premium-gradient {
            background: linear-gradient(135deg, #00236f 0%, #1e3a8a 100%);
        }

        .ambient-shadow {
            box-shadow: 0px 8px 24px rgba(26, 27, 33, 0.05);
        }

        .ambient-shadow-hover:hover {
            box-shadow: 0px 12px 32px rgba(26, 27, 33, 0.08);
        }
    </style>

    <div class="font-manrope space-y-10">
        <header class="flex flex-col gap-4 md:flex-row md:items-end md:justify-end">
            <div class="flex items-center gap-3">
                <span class="badge badge-primary bg-primary-50 text-primary-500 rounded-full px-4 py-2 text-xs font-bold uppercase tracking-widest">
                    {{ $submissions->count() }} Sertifikat
                </span>
            </div>
        </header>

        @if ($submissions->isEmpty())
            <div
                class="flex min-h-[400px] flex-col items-center justify-center rounded-[3rem] border-2 border-dashed border-gray-200 bg-gray-50/50 p-12 text-center dark:border-gray-700 dark:bg-gray-800/20">
                <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                        </path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Belum Ada Sertifikat</h3>
                <p class="mt-2 max-w-xs text-gray-500">Sertifikat Anda akan muncul di sini secara otomatis setelah pengajuan artikel Anda disetujui oleh tim redaksi.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($submissions as $record)
                    <!-- Submission Certificate Group Card -->
                    <div
                        class="ambient-shadow ambient-shadow-hover group flex flex-col gap-6 rounded-[2.5rem] border border-gray-100 bg-white p-8 transition-all duration-300 dark:border-gray-800 dark:bg-gray-900">
                        <!-- Card Header -->
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="bg-primary-50 dark:bg-primary-900/30 flex h-10 w-10 items-center justify-center rounded-xl">
                                    <svg class="text-primary-600 dark:text-primary-400 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-widest text-gray-400">Pengajuan #{{ $record->id }}</h4>
                                    <span class="text-[10px] font-medium text-gray-500">{{ $record->approved_date?->format('d M Y') }}</span>
                                </div>
                            </div>
                            <span
                                class="badge badge-primary flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:bg-blue-900/40 dark:text-blue-400">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                Terverifikasi
                            </span>
                        </div>

                        <!-- Card Content -->
                        <div class="flex-1">
                            <h3 class="group-hover:text-primary-600 line-clamp-2 text-lg font-bold leading-tight text-gray-900 transition-colors duration-300 dark:text-white">
                                {{ $record->title }}
                            </h3>
                            <p class="mt-3 text-xs font-medium italic text-gray-500">
                                Diterbitkan di <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $record->journal->name }}</span>
                            </p>
                        </div>

                        <!-- Download Actions Grid -->
                        <div class="grid grid-cols-1 gap-3">
                            <!-- Download LOA -->
                            <a href="{{ route('public.loa.preview', ['record' => $record, 'download' => 1]) }}" target="_blank"
                                class="premium-gradient ambient-shadow flex items-center justify-between gap-2 rounded-2xl px-6 py-4 text-sm font-bold text-white transition-all duration-300 hover:scale-[1.02] active:scale-95">
                                <div class="flex items-center gap-3">
                                    <svg class="h-5 w-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="uppercase tracking-tighter">Dokumen LOA</span>
                                </div>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </a>

                            <!-- Two Column Grid for AC and PFC -->
                            <!-- Award Certificate -->
                            <a href="{{ route('public.ac.preview', ['record' => $record, 'download' => 1]) }}" target="_blank"
                                class="premium-gradient ambient-shadow flex items-center justify-between gap-2 rounded-2xl px-6 py-4 text-sm font-bold text-white transition-all duration-300 hover:scale-[1.02] active:scale-95">
                                <div class="flex items-center gap-3">
                                    <svg class="h-5 w-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="uppercase tracking-tighter">Sertifikat Penulis (AC)</span>
                                </div>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>


                            </a>

                            <!-- Plagiarism Free -->
                            <a href="{{ route('public.pfc.preview', ['record' => $record, 'download' => 1]) }}" target="_blank"
                                class="premium-gradient ambient-shadow flex items-center justify-between gap-2 rounded-2xl px-6 py-4 text-sm font-bold text-white transition-all duration-300 hover:scale-[1.02] active:scale-95">
                                <div class="flex items-center gap-3">
                                    <svg class="h-5 w-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="uppercase tracking-tighter">Bebas Plagiarisme (PFC)</span>
                                </div>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>

                            </a>
                        </div>

                        <!-- Layanan Tambahan (Other Services) Action Bar -->
                        <div class="mt-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Layanan Tambahan</span>

                                {{-- Check pending requests --}}
                                @php
                                    $pendingReq = $record->serviceRequests?->where('status', 'pending')->first();
                                @endphp

                                @if ($pendingReq)
                                    <span class="animate-pulse rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                        {{ $pendingReq->service_type === 'add_doi' ? 'DOI Diajukan' : 'Ganti PDF Diajukan' }}
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                {{-- Request DOI Button (if not already having DOI) --}}
                                @if (!$record->has_doi)
                                    <button wire:click="openServiceModal({{ $record->id }}, 'add_doi')"
                                        class="flex items-center justify-center gap-1.5 rounded-xl border border-purple-200 bg-purple-50 px-3 py-2.5 text-xs font-bold text-purple-700 shadow-sm transition-all hover:bg-purple-100 dark:border-purple-800/60 dark:bg-purple-950/30 dark:text-purple-300">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        <span>Tambah DOI</span>
                                        <span class="text-[10px] opacity-75">(20rb)</span>
                                    </button>
                                @else
                                    <div class="flex items-center justify-center gap-1 rounded-xl bg-gray-50 px-3 py-2.5 text-xs font-medium text-gray-400 dark:bg-gray-800/50">
                                        <svg class="h-3.5 w-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <span>DOI Aktif</span>
                                    </div>
                                @endif

                                {{-- Update PDF Manuscript Button --}}
                                <button wire:click="openServiceModal({{ $record->id }}, 'update_pdf')"
                                    class="flex items-center justify-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2.5 text-xs font-bold text-blue-700 shadow-sm transition-all hover:bg-blue-100 dark:border-blue-800/60 dark:bg-blue-950/30 dark:text-blue-300">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                    </svg>
                                    <span>Ganti PDF</span>
                                    <span class="text-[10px] opacity-75">(25rb)</span>
                                </button>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif

        {{-- SERVICE REQUEST MODAL --}}
        @if ($showServiceModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
                <div class="max-h-[90vh] w-full max-w-lg space-y-5 overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                {{ $selectedServiceType === 'add_doi' ? 'Layanan Tambah DOI (Doi Saja)' : 'Layanan Ganti File PDF Naskah' }}
                            </h3>
                            <p class="mt-0.5 text-xs text-gray-500">
                                Pengajuan #{{ $selectedSubmissionId }}
                            </p>
                        </div>
                        <button wire:click="$set('showServiceModal', false)" class="text-gray-400 hover:text-gray-600">
                            ✕
                        </button>
                    </div>

                    {{-- QRIS & PRICE BOX --}}
                    <div class="flex flex-col items-center justify-center rounded-2xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/50">
                        <img src="{{ asset('assets/qris.jpg') }}" alt="QRIS" class="mb-2 w-full max-w-[220px] rounded-xl shadow-sm" />
                        <span class="block text-center text-[11px] text-gray-500">Scan QRIS di atas dengan m-Banking atau E-Wallet apa saja</span>
                        <div class="mt-3 text-center">
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Total Biaya Layanan:</span>
                            <div class="text-primary-600 dark:text-primary-400 font-mono text-xl font-extrabold">
                                {{ $selectedServiceType === 'add_doi' ? 'Rp 20.000' : 'Rp 25.000' }}
                            </div>
                        </div>
                    </div>

                    {{-- FORM INPUTS --}}
                    <form wire:submit.prevent="submitServiceRequest" class="space-y-4 text-xs">

                        {{-- UPLOAD NEW PDF (ONLY FOR GANTI PDF) --}}
                        @if ($selectedServiceType === 'update_pdf')
                            <div>
                                <label class="mb-1 block font-bold text-gray-700 dark:text-gray-300">
                                    Upload File PDF Naskah Baru <span class="text-red-500">*</span>
                                </label>
                                <input type="file" wire:model="newPdfFile" accept="application/pdf"
                                    class="w-full rounded-xl border border-gray-300 p-1 text-xs text-gray-500 file:mr-3 file:rounded-xl file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-100 dark:border-gray-700" />
                                @error('newPdfFile')
                                    <span class="mt-1 block text-[11px] text-red-500">{{ $message }}</span>
                                @enderror
                                <div wire:loading wire:target="newPdfFile" class="text-primary-600 mt-1 text-xs">Mengunggah PDF...</div>
                            </div>
                        @endif

                        {{-- UPLOAD BUKTI PEMBAYARAN --}}
                        <div>
                            <label class="mb-1 block font-bold text-gray-700 dark:text-gray-300">
                                Upload Bukti Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <input type="file" wire:model="paymentProof" accept="image/*"
                                class="w-full rounded-xl border border-gray-300 p-1 text-xs text-gray-500 file:mr-3 file:rounded-xl file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100 dark:border-gray-700" />
                            @error('paymentProof')
                                <span class="mt-1 block text-[11px] text-red-500">{{ $message }}</span>
                            @enderror
                            <div wire:loading wire:target="paymentProof" class="mt-1 text-xs text-emerald-600">Mengunggah Bukti Bayar...</div>
                        </div>

                        {{-- NOTES / IDENTIFIER --}}
                        <div>
                            <label class="mb-1 block font-bold text-gray-700 dark:text-gray-300">
                                Catatan / Keterangan (Opsional)
                            </label>
                            <textarea wire:model="serviceNotes" rows="2" class="w-full rounded-xl border-gray-300 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="{{ $selectedServiceType === 'add_doi' ? 'Tuliskan catatan request DOI jika ada...' : 'Tuliskan bagian apa saja yang diperbaiki pada PDF baru...' }}"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                            <button type="button" wire:click="$set('showServiceModal', false)" class="px-4 py-2 text-xs font-bold text-gray-600 hover:text-gray-800">
                                Batal
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="bg-primary-600 hover:bg-primary-700 flex items-center gap-1.5 rounded-xl px-5 py-2.5 text-xs font-bold text-white shadow-sm">
                                <span wire:loading.remove wire:target="submitServiceRequest">Kirim Pengajuan</span>
                                <span wire:loading wire:target="submitServiceRequest">Memproses...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
