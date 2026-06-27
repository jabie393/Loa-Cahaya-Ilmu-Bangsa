<x-filament-panels::page>
    <style>
        .markdown-content ul {
            list-style-type: disc !important;
            padding-left: 1.5rem !important;
            margin-top: 0.5rem !important;
            margin-bottom: 0.5rem !important;
        }
        .markdown-content ol {
            list-style-type: decimal !important;
            padding-left: 1.5rem !important;
            margin-top: 0.5rem !important;
            margin-bottom: 0.5rem !important;
        }
        .markdown-content li {
            margin-top: 0.25rem !important;
            margin-bottom: 0.25rem !important;
            line-height: 1.6 !important;
        }
        .markdown-content p {
            margin-top: 0.5rem !important;
            margin-bottom: 0.5rem !important;
            line-height: 1.6 !important;
        }
        .markdown-content strong {
            font-weight: 700 !important;
        }
    </style>
    @php
        $formatMarkdown = function($text) {
            if (empty($text)) return '-';
            // Ensure newlines before list items if AI returned them on the same line
            $text = preg_replace('/(?<!\n)(\s+)(\d+\.\s+\*\*)/', "\n$2", $text);
            return \Illuminate\Support\Str::markdown($text);
        };
    @endphp
    <div class="space-y-6">
        @if (Auth::user()->hasRole('super_admin') && $record->status !== 'Approved' && $record->status !== 'Rejected')
            <div class="bg-primary-50 dark:bg-primary-900/20 border-primary-100 dark:border-primary-900/30 flex items-center gap-3 rounded-xl border p-4">
                <svg class="text-primary-600 dark:text-primary-400 h-6 w-6"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-primary-900 dark:text-primary-100 text-md font-medium">Silahkan review data pengajuan dan bukti pembayaran di bawah ini.</p>
            </div>
        @endif
        @if ($record->status == 'Approved')
            <div class="bg-success-50 dark:bg-success-900/20 border-success-100 dark:border-success-900/30 flex items-center gap-3 rounded-xl border p-4">
                <svg class="text-success-600 dark:text-success-400 h-6 w-6"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-success-900 dark:text-success-100 text-md font-medium">Pengajuan Anda telah disetujui.</p>
            </div>
        @endif
        @if ($record->status == 'Rejected')
            <div class="bg-danger-50 dark:bg-danger-900/20 border-danger-100 dark:border-danger-900/30 flex flex-col gap-3 rounded-xl border p-4">
                <div class="flex items-center gap-3">
                    <svg class="text-danger-600 dark:text-danger-400 h-6 w-6"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-danger-900 dark:text-danger-100 text-md font-medium">Pengajuan Anda ditolak.</p>
                </div>
                @if ($record->rejection_reason)
                    <div class="ml-9 space-y-2">
                        <p class="text-danger-800 dark:text-danger-200 text-sm font-semibold">Alasan Penolakan:</p>
                        <p class="text-danger-700 dark:text-danger-300 text-sm">{{ $record->rejection_reason }}</p>
                    </div>
                @endif
            </div>
        @endif
        @if ($record->proof_of_payment == null && $record->status == 'Pending')
            <div class="bg-warning-50 dark:bg-warning-900/20 border-warning-100 dark:border-warning-900/30 flex items-center gap-3 rounded-xl border p-4">
                <svg class="text-warning-600 dark:text-warning-400 h-6 w-6"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-warning-900 dark:text-warning-100 text-md font-medium">Silahkan upload bukti pembayaran.</p>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 pb-6 md:grid-cols-5">
            <!-- Review Data Left Column -->
            <div class="space-y-4 md:col-span-3">
                @if ($record->review_status === 'reviewed')
                    <!-- Hasil Review Naskah -->
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Hasil Review Naskah</h4>
                    <div class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col">
                                <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Status Review</span>
                                <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-900/20 dark:text-green-400 mt-1 w-max">
                                    {{ strtoupper($record->review_status) }}
                                </span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Laporan Terkirim</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                                    {{ $record->review_email_sent_at ? $record->review_email_sent_at->format('d M Y H:i:s') : '-' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="wrap-break-word flex flex-col pt-2 border-t border-gray-100 dark:border-gray-700/50">
                            <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Saran Perbaikan Umum</span>
                            <div class="text-sm text-gray-900 dark:text-white mt-1 leading-relaxed markdown-content">
                                {!! $formatMarkdown($record->general_suggestions) !!}
                            </div>
                        </div>

                        <!-- Detail Penilaian Struktur -->
                        <div class="pt-2 border-t border-gray-100 dark:border-gray-700/50">
                            <details class="group">
                                <summary class="flex justify-between items-center font-bold text-xs uppercase text-gray-400 dark:text-gray-500 cursor-pointer list-none">
                                    <span>Detail Penilaian Struktur</span>
                                    <span class="transition group-open:rotate-180">
                                        <svg fill="none" height="16" width="16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"></path></svg>
                                    </span>
                                </summary>
                                <div class="mt-4 space-y-4 pl-2 border-l-2 border-gray-100 dark:border-gray-700">
                                    @if($record->structure_review)
                                        <div class="flex flex-col">
                                            <span class="text-[11px] font-bold uppercase text-gray-400 dark:text-gray-500">Struktur Naskah</span>
                                            <div class="text-xs text-gray-900 dark:text-white mt-0.5 leading-relaxed markdown-content">
                                                {!! $formatMarkdown($record->structure_review) !!}
                                            </div>
                                        </div>
                                    @endif
                                    @if($record->abstract_review)
                                        <div class="flex flex-col pt-2 border-t border-gray-550 dark:border-gray-700/30">
                                            <span class="text-[11px] font-bold uppercase text-gray-400 dark:text-gray-500">Analisis Abstrak</span>
                                            <div class="text-xs text-gray-900 dark:text-white mt-0.5 leading-relaxed markdown-content">
                                                {!! $formatMarkdown($record->abstract_review) !!}
                                            </div>
                                        </div>
                                    @endif
                                    @if($record->introduction_review)
                                        <div class="flex flex-col pt-2 border-t border-gray-550 dark:border-gray-700/30">
                                            <span class="text-[11px] font-bold uppercase text-gray-400 dark:text-gray-500">Analisis Pendahuluan</span>
                                            <div class="text-xs text-gray-900 dark:text-white mt-0.5 leading-relaxed markdown-content">
                                                {!! $formatMarkdown($record->introduction_review) !!}
                                            </div>
                                        </div>
                                    @endif
                                    @if($record->method_review)
                                        <div class="flex flex-col pt-2 border-t border-gray-550 dark:border-gray-700/30">
                                            <span class="text-[11px] font-bold uppercase text-gray-400 dark:text-gray-500">Analisis Metode</span>
                                            <div class="text-xs text-gray-900 dark:text-white mt-0.5 leading-relaxed markdown-content">
                                                {!! $formatMarkdown($record->method_review) !!}
                                            </div>
                                        </div>
                                    @endif
                                    @if($record->results_review)
                                        <div class="flex flex-col pt-2 border-t border-gray-550 dark:border-gray-700/30">
                                            <span class="text-[11px] font-bold uppercase text-gray-400 dark:text-gray-500">Analisis Hasil & Pembahasan</span>
                                            <div class="text-xs text-gray-900 dark:text-white mt-0.5 leading-relaxed markdown-content">
                                                {!! $formatMarkdown($record->results_review) !!}
                                            </div>
                                        </div>
                                    @endif
                                    @if($record->conclusion_review)
                                        <div class="flex flex-col pt-2 border-t border-gray-550 dark:border-gray-700/30">
                                            <span class="text-[11px] font-bold uppercase text-gray-400 dark:text-gray-500">Analisis Kesimpulan</span>
                                            <div class="text-xs text-gray-900 dark:text-white mt-0.5 leading-relaxed markdown-content">
                                                {!! $formatMarkdown($record->conclusion_review) !!}
                                            </div>
                                        </div>
                                    @endif
                                    @if($record->bibliography_review)
                                        <div class="flex flex-col pt-2 border-t border-gray-550 dark:border-gray-700/30">
                                            <span class="text-[11px] font-bold uppercase text-gray-400 dark:text-gray-500">Analisis Daftar Pustaka</span>
                                            <div class="text-xs text-gray-900 dark:text-white mt-0.5 leading-relaxed markdown-content">
                                                {!! $formatMarkdown($record->bibliography_review) !!}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </details>
                        </div>
                    </div>
                @endif

                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Informasi Penulis & Publikasi</h4>
                <div class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800">
                    <div class="wrap-break-word flex flex-col">
                        <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Judul Artikel</span>
                        <span class="text-md font-semibold text-gray-900 dark:text-white">{{ $record->title }}</span>
                    </div>
                    <div class="wrap-break-word flex flex-col">
                        <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Nama Penulis</span>
                        <span class="text-md font-semibold text-gray-900 dark:text-white">
                             @php
                                $authors = $record->author_name;
                                $authorsArray = $record->authors;
                                if (is_string($authors) && str_starts_with($authors, '[') && str_ends_with($authors, ']')) {
                                    $decoded = json_decode($authors, true);
                                    if (is_array($decoded)) {
                                        $authors = $decoded;
                                    }
                                }
                            @endphp
                            @if (!empty($authorsArray) && is_array($authorsArray))
                                <ul class="list-disc pl-4 space-y-1">
                                    @foreach ($authorsArray as $author)
                                        <li>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $author['name'] ?? '' }}</span>
                                            @if(!empty($author['institution']))
                                                <span class="text-xs text-gray-500 dark:text-gray-400">({{ $author['institution'] }})</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                @php
                                    if (is_array($authors)) {
                                        $authors = implode(', ', $authors);
                                    }
                                @endphp
                                {{ $authors }}
                            @endif
                        </span>
                    </div>
                    <div class="wrap-break-word flex flex-col">
                        <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Email</span>
                        <span class="text-md font-semibold text-gray-900 dark:text-white">{{ $record->email }}</span>
                    </div>
                    <div class="wrap-break-word grid grid-cols-2 gap-4">
                        <div class="flex flex-col">
                            <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Jurnal</span>
                            <span class="text-md font-semibold text-gray-900 dark:text-white">{{ $record->journal?->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Volume</span>
                            <span class="text-md font-semibold text-gray-900 dark:text-white">{{ $record->volume }}</span>
                        </div>
                    </div>
                    <div class="wrap-break-word flex flex-col">
                        <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Link Publikasi</span>
                        <a href="{{ $record->publication_link ?? '#' }}"
                           target="_blank"
                           class="text-primary-600 dark:text-primary-400 truncate text-xs font-medium">{{ $record->publication_link ?? 'Belum diisi' }}</a>
                    </div>
                </div>

                <!-- Metadata Artikel -->
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mt-6">Metadata Artikel</h4>
                <div class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800">
                    <div class="wrap-break-word flex flex-col">
                        <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Keywords</span>
                        <div class="flex flex-wrap gap-1 mt-1">
                            @if ($record->keywords)
                                @foreach (explode(',', $record->keywords) as $keyword)
                                    <span class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 ring-1 ring-inset ring-gray-500/10">
                                        {{ trim($keyword) }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-sm text-gray-900 dark:text-white">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="wrap-break-word flex flex-col pt-2">
                        <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Abstract</span>
                        <span class="text-sm text-gray-900 dark:text-white mt-1 whitespace-pre-line leading-relaxed">{{ $record->abstract ?? '-' }}</span>
                    </div>
                    <div class="wrap-break-word flex flex-col pt-2">
                        <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Referensi / Daftar Pustaka</span>
                        <span class="text-sm text-gray-900 dark:text-white mt-1 whitespace-pre-line leading-relaxed">{{ $record->references ?? '-' }}</span>
                    </div>
                </div>

                <!-- OJS Integration -->
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mt-6">OJS Integration</h4>
                <div class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col">
                            <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">OJS Submission ID</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white mt-1">{{ $record->ojs_submission_id ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">OJS Status</span>
                            <div class="mt-1">
                                @if($record->ojs_status)
                                    <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{
                                        match($record->ojs_status) {
                                            'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/20 dark:text-amber-400 dark:ring-amber-500/30',
                                            'submitted' => 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-900/20 dark:text-blue-400 dark:ring-blue-500/30',
                                            'accepted' => 'bg-purple-50 text-purple-700 ring-purple-600/20 dark:bg-purple-900/20 dark:text-purple-400 dark:ring-purple-500/30',
                                            'published' => 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-900/20 dark:text-green-400 dark:ring-green-500/30',
                                            'failed' => 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-900/20 dark:text-red-400 dark:ring-red-500/30',
                                            default => 'bg-gray-50 text-gray-600 ring-gray-500/10 dark:bg-gray-700/50 dark:text-gray-400 dark:ring-gray-600/20',
                                        }
                                    }}">
                                        {{ $record->ojs_status }}
                                    </span>
                                @else
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">-</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="wrap-break-word flex flex-col">
                        <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Last Sync</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                            {{ $record->ojs_synced_at ? $record->ojs_synced_at->format('d M Y H:i:s') : '-' }}
                        </span>
                    </div>
                    <div class="wrap-break-word flex flex-col">
                        <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Error Message</span>
                        <span class="text-sm font-medium mt-1 {{ $record->ojs_error_message ? 'text-danger-600 dark:text-danger-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $record->ojs_error_message ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Review Right Column -->
            <div class="space-y-4 md:col-span-2">
                @if ($record->manuscript_file)
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">File PDF Naskah</h4>
                        <div class="flex flex-col rounded-2xl border border-gray-100 bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-3 text-red-600 dark:text-red-400">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate dark:text-white">
                                        {{ basename($record->manuscript_file) }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500">Naskah Terunggah</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <x-filament::button
                                    href="{{ Storage::disk('public')->url($record->manuscript_file) }}"
                                    tag="a"
                                    download
                                    target="_blank"
                                    icon="heroicon-m-arrow-down-tray"
                                    color="primary"
                                    class="w-full"
                                >
                                    Download File PDF
                                </x-filament::button>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($record->ojs_username)
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Akun Penulis OJS</h4>
                        <div class="flex flex-col rounded-2xl border border-gray-100 bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800">
                            <div class="space-y-3">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-gray-500">Username/Email:</span>
                                    <span class="font-mono font-semibold text-gray-900 dark:text-white">{{ $record->ojs_username }} ({{ $record->email }})</span>
                                </div>
                                @if ($record->ojs_password)
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-gray-500">Password OJS:</span>
                                        <span class="font-mono font-semibold text-primary-600 dark:text-primary-400">{{ $record->ojs_password }}</span>
                                    </div>
                                    <p class="text-[11px] text-amber-600 dark:text-amber-400 italic mt-1 leading-normal">
                                        *Password ini digenerate secara otomatis karena akun belum terdaftar sebelumnya di OJS.
                                    </p>
                                @else
                                    <p class="text-[11px] text-gray-600 dark:text-gray-300 mt-2 leading-relaxed">
                                        untuk login silakan menggunakan akun ojs yang sudah ada sesuai dengan email berikut.
                                    </p>
                                    <div class="flex justify-between items-center text-xs pt-2 border-t border-gray-100 dark:border-gray-700/50">
                                        <span class="text-gray-500">Email:</span>
                                        <span class="font-mono font-semibold text-gray-900 dark:text-white">{{ $record->email }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if ($record->status === 'Approved')
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Certificates</h4>
                        <div class="flex min-h-[200px] flex-col items-center justify-around gap-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800">
                            <a href="{{ route('public.loa.preview', ['record' => $record, 'download' => 1]) }}"
                               target="_blank"
                               class="h-25 hover:border-primary hover:scale-101 group flex w-full items-center gap-2 rounded-xl border-l-4 border-gray-400 px-6 py-6 font-bold shadow-sm transition duration-300 dark:bg-gray-900 dark:text-white">
                                <div class="color-white group-hover:bg-primary-500 rounded-lg bg-gray-200 p-2 transition duration-300">
                                    <svg class="h-10 w-10 transition duration-300 group-hover:text-white dark:text-black"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                </div>
                                <div class="pl-5">
                                    <p>
                                        Letter of Acceptance
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Download
                                    </p>
                                </div>
                            </a>
                            <a href="{{ route('public.ac.preview', ['record' => $record, 'download' => 1]) }}"
                               target="_blank"
                               class="h-25 hover:border-primary hover:scale-101 group flex w-full items-center gap-2 rounded-xl border-l-4 border-gray-400 px-6 py-6 font-bold shadow-sm transition duration-300 dark:bg-gray-900 dark:text-white">
                                <div class="color-white group-hover:bg-primary-500 rounded-lg bg-gray-200 p-2 transition duration-300">
                                    <svg class="h-10 w-10 transition duration-300 group-hover:text-white dark:text-black"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                </div>
                                <div class="pl-5">
                                    <p>
                                        Author's Certificate
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Download
                                    </p>
                                </div>
                            </a>
                            <a href="{{ route('public.pfc.preview', ['record' => $record, 'download' => 1]) }}"
                               target="_blank"
                               class="h-25 hover:border-primary hover:scale-101 group flex w-full items-center gap-2 rounded-xl border-l-4 border-gray-400 px-6 py-6 font-bold shadow-sm transition duration-300 dark:bg-gray-900 dark:text-white">
                                <div class="color-white group-hover:bg-primary-500 rounded-lg bg-gray-200 p-2 transition duration-300">
                                    <svg class="h-10 w-10 transition duration-300 group-hover:text-white dark:text-black"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                </div>
                                <div class="pl-5">
                                    <p>
                                        Plagiarism-Free Certificate
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Download
                                    </p>
                                </div>
                            </a>

                        </div>
                    </div>
                @else
                @endif
                @if ($record->status === 'Approved')
                @else
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Bukti Pembayaran</h4>
                        <div class="flex min-h-[200px] flex-col items-center justify-center rounded-2xl border border-gray-100 bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800">
                            @if ($record->proof_of_payment && $record->status !== 'Approved')
                                <div class="group relative">
                                    <img src="{{ Storage::disk('public')->url($record->proof_of_payment) }}"
                                         alt="Bukti Pembayaran"
                                         class="max-h-[250px] max-w-full rounded-lg border border-gray-200 shadow-md dark:border-gray-600">
                                    <div class="absolute inset-0 flex items-center justify-center rounded-lg bg-black/40 opacity-0 backdrop-blur-[2px] transition duration-300 group-hover:opacity-100">
                                        <button class="btn"
                                                onclick="my_modal_2.showModal()">Lihat</button>
                                        <dialog id="my_modal_2"
                                                class="modal">
                                            <div class="modal-box h-auto max-w-4xl">
                                                <img src="{{ Storage::disk('public')->url($record->proof_of_payment) }}"
                                                     alt="Bukti Pembayaran"
                                                     class="h-full w-full rounded-lg border border-gray-200 shadow-md dark:border-gray-600">
                                            </div>
                                            <form method="dialog"
                                                  class="modal-backdrop">
                                                <button>close</button>
                                            </form>
                                        </dialog>


                                    </div>

                                </div>
                            @else
                                <div class="py-10 text-center">
                                    <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-900/50">
                                        <svg class="h-8 w-8 text-gray-300"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-medium italic text-gray-400">Belum ada file bukti pembayaran, silahkan edit untuk di tambah.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @php
                    // 1. LOA Badge Info
                    $loaStatus = $record->status;
                    $loaClass = match ($loaStatus) {
                        'Approved' => 'text-emerald-700 bg-emerald-50 border-emerald-200 dark:text-emerald-400 dark:bg-emerald-950/30 dark:border-emerald-800/50',
                        'Rejected' => 'text-red-700 bg-red-50 border-red-200 dark:text-red-400 dark:bg-red-950/30 dark:border-red-800/50',
                        'Draft' => 'text-gray-700 bg-gray-50 border-gray-200 dark:text-gray-400 dark:bg-gray-800/30 dark:border-gray-700/50',
                        default => 'text-amber-700 bg-amber-50 border-amber-200 dark:text-amber-400 dark:bg-amber-950/30 dark:border-amber-800/50' // Pending
                    };

                    // 2. AI Review Badge Info
                    $reviewStatus = $record->review_status;
                    if ($reviewStatus !== 'reviewed') {
                        if ($record->status === 'Approved' || !empty($record->ojs_status)) {
                            $reviewStatus = 'N/A';
                        } elseif (empty($reviewStatus)) {
                            $reviewStatus = 'pending';
                        }
                    }

                    $reviewClass = match ($reviewStatus) {
                        'pending' => 'text-gray-700 bg-gray-50 border-gray-200 dark:text-gray-400 dark:bg-gray-850/30 dark:border-gray-800/50',
                        'processing' => 'text-amber-700 bg-amber-50 border-amber-200 dark:text-amber-400 dark:bg-amber-950/30 dark:border-amber-800/50',
                        'reviewed' => 'text-emerald-700 bg-emerald-50 border-emerald-200 dark:text-emerald-400 dark:bg-emerald-950/30 dark:border-emerald-800/50',
                        'failed' => 'text-red-700 bg-red-50 border-red-200 dark:text-red-400 dark:bg-red-950/30 dark:border-red-800/50',
                        'N/A' => 'text-gray-500 bg-gray-50 border-gray-200 dark:text-gray-400 dark:bg-gray-800/30 dark:border-gray-700/50',
                        default => 'text-gray-700 bg-gray-50 border-gray-200 dark:text-gray-400 dark:bg-gray-850/30 dark:border-gray-850/50'
                    };
                    $reviewLabel = $reviewStatus === 'N/A' ? 'N/A' : ucfirst($reviewStatus);

                    // 3. OJS Badge Info
                    $ojsStatus = $record->ojs_status ?? 'Not Sent';
                    $ojsClass = match ($record->ojs_status) {
                        'pending' => 'text-amber-700 bg-amber-50 border-amber-200 dark:text-amber-400 dark:bg-amber-950/30 dark:border-amber-800/50',
                        'submitted' => 'text-sky-700 bg-sky-50 border-sky-200 dark:text-sky-400 dark:bg-sky-950/30 dark:border-sky-800/50',
                        'accepted' => 'text-indigo-700 bg-indigo-50 border-indigo-200 dark:text-indigo-400 dark:bg-indigo-950/30 dark:border-indigo-800/50',
                        'published' => 'text-emerald-700 bg-emerald-50 border-emerald-200 dark:text-emerald-400 dark:bg-emerald-950/30 dark:border-emerald-800/50',
                        'failed' => 'text-red-700 bg-red-50 border-red-200 dark:text-red-400 dark:bg-red-950/30 dark:border-red-800/50',
                        default => 'text-gray-700 bg-gray-50 border-gray-200 dark:text-gray-400 dark:bg-gray-800/30 dark:border-gray-700/50' // null / not sent
                    };
                    $ojsLabel = ucfirst($ojsStatus);
                @endphp

                <div class="space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Status Pengajuan</h4>
                    <div class="flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/50 pb-3">
                            <span class="text-xs font-bold uppercase text-gray-400 dark:text-gray-500">Status Review</span>
                            <span class="inline-flex items-center justify-center w-[85px] py-0.5 text-[10px] font-semibold rounded-full border {{ $reviewClass }}">
                                {{ $reviewLabel }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/50 pb-3">
                            <span class="text-xs font-bold uppercase text-gray-400 dark:text-gray-500">Status LOA</span>
                            <span class="inline-flex items-center justify-center w-[85px] py-0.5 text-[10px] font-semibold rounded-full border {{ $loaClass }}">
                                {{ $loaStatus }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase text-gray-400 dark:text-gray-500">Status OJS</span>
                            <span class="inline-flex items-center justify-center w-[85px] py-0.5 text-[10px] font-semibold rounded-full border {{ $ojsClass }}">
                                {{ $ojsLabel }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-filament-panels::page>
