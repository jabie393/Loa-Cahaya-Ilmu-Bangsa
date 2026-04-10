<x-filament-panels::page>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

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
        <header class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-primary-600 dark:text-primary-400 text-3xl font-extrabold tracking-tight">Papan Sertifikat Digital</h1>
                <p class="text-md mt-2 max-w-xl leading-relaxed text-gray-500 dark:text-gray-400">
                    Koleksi sertifikat resmi atas publikasi artikel Anda. Di sini Anda dapat mengunduh Letter of Acceptance (LoA), Sertifikat Penulis, dan Sertifikat Bebas Plagiarisme untuk setiap karya yang telah disetujui.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-gray-100 px-4 py-2 text-xs font-bold uppercase tracking-widest text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                    {{ $submissions->count() }} Sertifikat
                </span>
            </div>
        </header>

        @if ($submissions->isEmpty())
            <div class="flex min-h-[400px] flex-col items-center justify-center rounded-[3rem] border-2 border-dashed border-gray-200 bg-gray-50/50 p-12 text-center dark:border-gray-700 dark:bg-gray-800/20">
                <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800">
                    <svg class="h-10 w-10"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.5"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Belum Ada Sertifikat</h3>
                <p class="mt-2 max-w-xs text-gray-500">Sertifikat Anda akan muncul di sini secara otomatis setelah pengajuan artikel Anda disetujui oleh tim redaksi.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($submissions as $record)
                    <!-- Submission Certificate Group Card -->
                    <div class="ambient-shadow ambient-shadow-hover group flex flex-col gap-6 rounded-[2.5rem] border border-gray-100 bg-white p-8 transition-all duration-300 dark:border-gray-800 dark:bg-gray-900">
                        <!-- Card Header -->
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="bg-primary-50 dark:bg-primary-900/30 flex h-10 w-10 items-center justify-center rounded-xl">
                                    <svg class="text-primary-600 dark:text-primary-400 h-6 w-6"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-widest text-gray-400">Pengajuan #{{ $record->id }}</h4>
                                    <span class="text-[10px] font-medium text-gray-500">{{ $record->approved_date?->format('d M Y') }}</span>
                                </div>
                            </div>
                            <span class="flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:bg-blue-900/40 dark:text-blue-400">
                                <svg class="h-3 w-3"
                                     fill="currentColor"
                                     viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
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
                            <a href="{{ route('public.loa.preview', ['record' => $record, 'print' => 1]) }}"
                               target="_blank"
                               class="premium-gradient ambient-shadow flex items-center justify-between gap-2 rounded-2xl px-6 py-4 text-sm font-bold text-white transition-all duration-300 hover:scale-[1.02] active:scale-95">
                                <div class="flex items-center gap-3">
                                    <svg class="h-5 w-5 opacity-80"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="uppercase tracking-tighter">Dokumen LOA</span>
                                </div>
                                <svg class="h-4 w-4"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="3"
                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </a>

                            <!-- Two Column Grid for AC and PFC -->
                                <!-- Award Certificate -->
                                <a href="{{ route('public.ac.preview', ['record' => $record, 'print' => 1]) }}"
                                   target="_blank"
                                   class="premium-gradient ambient-shadow flex items-center justify-between gap-2 rounded-2xl px-6 py-4 text-sm font-bold text-white transition-all duration-300 hover:scale-[1.02] active:scale-95">
                                    <div class="flex items-center gap-3">
                                    <svg class="h-5 w-5 opacity-80"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="uppercase tracking-tighter">Sertifikat Penulis (AC)</span>
                                </div>
                                <svg class="h-4 w-4"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="3"
                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                    
                                    
                                </a>

                                <!-- Plagiarism Free -->
                                <a href="{{ route('public.pfc.preview', ['record' => $record, 'print' => 1]) }}"
                                   target="_blank"
                                   class="premium-gradient ambient-shadow flex items-center justify-between gap-2 rounded-2xl px-6 py-4 text-sm font-bold text-white transition-all duration-300 hover:scale-[1.02] active:scale-95">
                                    <div class="flex items-center gap-3">
                                    <svg class="h-5 w-5 opacity-80"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="uppercase tracking-tighter">Bebas Plagiarisme (PFC)</span>
                                </div>
                                <svg class="h-4 w-4"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="3"
                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                    
                                </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</x-filament-panels::page>
