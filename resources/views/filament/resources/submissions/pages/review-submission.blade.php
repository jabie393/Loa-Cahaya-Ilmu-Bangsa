<x-filament-panels::page>
    <div class="space-y-6">
        @if (Auth::user()->hasRole('super_admin') && $record->status !== 'Approved')
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
        @if ($record->proof_of_payment == null)
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
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Informasi Penulis & Publikasi</h4>
                <div class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800">
                    <div class="wrap-break-word flex flex-col">
                        <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Judul Artikel</span>
                        <span class="text-md font-semibold text-gray-900 dark:text-white">{{ $record->title }}</span>
                    </div>
                    <div class="wrap-break-word flex flex-col">
                        <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Nama Penulis</span>
                        <span class="text-md font-semibold text-gray-900 dark:text-white">{{ $record->author_name }}</span>
                    </div>
                    <div class="wrap-break-word flex flex-col">
                        <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Email</span>
                        <span class="text-md font-semibold text-gray-900 dark:text-white">{{ $record->email }}</span>
                    </div>
                    <div class="wrap-break-word flex flex-col">
                        <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Instansi</span>
                        <span class="text-md font-semibold text-gray-900 dark:text-white">{{ $record->institution }}</span>
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
            </div>

            <!-- Review Right Column -->
            <div class="space-y-4 md:col-span-2">
                @if ($record->status === 'Approved')
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Letter of Acceptance (LOA)</h4>
                        <div class="flex min-h-[200px] flex-col items-center justify-center rounded-2xl border border-gray-100 bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800">
                            <a href="#"
                               target="_blank"
                               class="bg-primary-600 hover:bg-primary-500 inline-flex items-center gap-2 rounded-xl px-6 py-3 font-bold text-white shadow-sm transition">
                                <svg class="h-5 w-5"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Download LOA PDF
                            </a>
                        </div>
                    </div>
                @else
                @endif
                <div class="space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Bukti Pembayaran</h4>
                    <div class="flex min-h-[200px] flex-col items-center justify-center rounded-2xl border border-gray-100 bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800">
                        @if ($record->status === 'Approved')
                            <p class="text-gray-500 dark:text-gray-400">Pembayaran sudah disetujui</p>
                        @elseif ($record->proof_of_payment)
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
            </div>
        </div>
    </div>
</x-filament-panels::page>
