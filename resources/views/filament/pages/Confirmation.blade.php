<div class="space-y-6">
    <div class="bg-primary-50 dark:bg-primary-900/20 border-primary-100 dark:border-primary-900/30 flex items-center gap-3 rounded-xl border p-4">
        <svg class="text-primary-600 dark:text-primary-400 h-6 w-6"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="text-primary-900 dark:text-primary-100 text-sm font-medium">Silakan periksa kembali data Anda sebelum melakukan pengiriman.</p>
    </div>

    <div class="grid grid-cols-1 gap-6 pb-6 md:grid-cols-2">
        <!-- Review Data Left Column -->
        <div class="space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Informasi Penulis & Publikasi</h4>
                <div class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800">
                    <div class="wrap-break-word flex flex-col">
                        <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Nama Penulis</span>
                        <span class="text-md font-semibold text-gray-900 dark:text-white">{{ $get('author_name') }}</span>
                    </div>
                    <div class="wrap-break-word flex flex-col">
                        <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Email</span>
                        <span class="text-md font-semibold text-gray-900 dark:text-white">{{ $get('email') }}</span>
                    </div>
                    <div class="wrap-break-word flex flex-col">
                        <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Judul Artikel</span>
                        <span class="text-md font-semibold text-gray-900 dark:text-white">{{ $get('title') }}</span>
                    </div>
                    <div class="wrap-break-word flex flex-col">
                        <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Instansi</span>
                        <span class="text-md font-semibold text-gray-900 dark:text-white">{{ $get('institution') }}</span>
                    </div>
                    <div class="wrap-break-word grid grid-cols-2 gap-4">
                        <div class="flex flex-col">
                            <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Jurnal</span>
                            <span class="text-md font-semibold text-gray-900 dark:text-white">{{ $get('journal?->name') ?? 'N/A' }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Volume</span>
                            <span class="text-md font-semibold text-gray-900 dark:text-white">{{ $get('volume') }}</span>
                        </div>
                    </div>
                    <div class="wrap-break-word flex flex-col">
                        <span class="text-[12px] font-bold uppercase text-gray-400 dark:text-gray-500">Link Publikasi</span>
                        <a href="{{ $get('publication_link') ?? '#' }}"
                           target="_blank"
                           class="text-primary-600 dark:text-primary-400 truncate text-xs font-medium">{{ $get('publication_link') ?? 'Belum diisi' }}</a>
                    </div>
                </div>
            </div>

        <!-- Review Right Column -->
        <div class="space-y-4">
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Bukti Pembayaran</h4>
            <div class="flex min-h-[200px] flex-col items-center justify-center rounded-2xl border border-gray-100 bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800">
                @php
                    $proof = $get('proof_of_payment');
                    // Handle array or string case for FileUpload component
                    if (is_array($proof)) {
                        $proof = reset($proof);
                    }
                @endphp

                @if ($proof)
                    <div class="group relative">
                        <img src="{{ Storage::disk('public')->url($proof) }}"
                             alt="Bukti Pembayaran"
                             class="max-h-[350px] max-w-full rounded-lg border border-gray-200 shadow-md dark:border-gray-600">
                        <div class="absolute inset-0 flex items-center justify-center rounded-lg bg-black/40 opacity-0 backdrop-blur-[2px] transition duration-300 group-hover:opacity-100">
                            <a href="{{ Storage::disk('public')->url($proof) }}"
                               target="_blank"
                               class="bg-primary-600 hover:bg-primary-500 rounded-full px-4 py-2 text-[10px] font-bold uppercase tracking-wider text-white shadow-lg">Lihat Ukuran Penuh</a>
                        </div>
                    </div>
                    <p class="mt-3 text-center text-[10px] italic text-gray-400">Tinjauan bukti pembayaran yang Anda unggah</p>
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
                        <p class="text-xs font-medium italic text-gray-400">Belum ada file bukti pembayaran diunggah</p>
                    </div>
                @endif
            </div>

            <div class="rounded-lg border-l-4 border-amber-400 bg-amber-50 p-4 dark:bg-amber-900/20">
                <p class="text-[11px] font-medium leading-relaxed text-amber-800 dark:text-amber-300">
                    <span class="mb-1 block font-bold italic underline">Pernyataan:</span>
                    Dengan menekan tombol <strong>Create</strong>, saya menyatakan bahwa seluruh data di atas adalah benar dan bukti pembayaran yang saya lampirkan adalah valid.
                </p>
            </div>
        </div>
    </div>
</div>
