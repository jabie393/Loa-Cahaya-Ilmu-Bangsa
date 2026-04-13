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
                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Nama Penulis</span>
                        <span class="text-sm font-semibold text-gray-900 break-words dark:text-white">{{ $get('author_name') }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Email</span>
                        <span class="text-sm font-semibold text-gray-900 break-words dark:text-white">{{ $get('email') }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Judul Artikel</span>
                        <span class="text-sm font-semibold text-gray-900 break-words dark:text-white">{{ $get('title') }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Instansi</span>
                        <span class="text-sm font-semibold text-gray-900 break-words dark:text-white">{{ $get('institution') }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Jurnal</span>
                            <span class="text-sm font-semibold text-gray-900 break-words dark:text-white">
                                {{ \App\Models\Journal::find($get('journal_id'))?->name ?? 'N/A' }}
                            </span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Volume</span>
                            <span class="text-sm font-semibold text-gray-900 break-words dark:text-white">{{ $get('volume') }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col pt-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Link Publikasi</span>
                        @if($get('publication_link'))
                            <a href="{{ $get('publication_link') }}"
                               target="_blank"
                               class="text-primary-600 dark:text-primary-400 text-xs font-medium hover:underline flex items-center gap-1">
                                <span class="truncate">{{ $get('publication_link') }}</span>
                                <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        @else
                            <span class="text-xs italic text-gray-400">Belum diisi</span>
                        @endif
                    </div>
                </div>
            </div>

        <!-- Review Right Column -->
        <div class="space-y-4">
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Bukti Pembayaran</h4>
            <div class="flex min-h-[200px] flex-col items-center justify-center rounded-2xl border border-gray-100 bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-800">
                @php
                    $proof = $get('proof_of_payment');
                    if (is_array($proof)) {
                        $proof = reset($proof);
                    }
                    
                    $proofUrl = null;
                    if ($proof) {
                        try {
                            // In case of string path (standard FileUpload state)
                            if (is_string($proof)) {
                                $proofUrl = Storage::disk('public')->url($proof);
                            } 
                            // In case of Livewire TemporaryUploadedFile object
                            elseif (method_exists($proof, 'temporaryUrl')) {
                                $proofUrl = $proof->temporaryUrl();
                            }
                        } catch (\Exception $e) {
                            $proofUrl = null;
                        }
                    }
                @endphp

                @if ($proofUrl)
                    <div class="group relative overflow-hidden rounded-xl border border-gray-100 shadow-sm transition-all hover:shadow-md dark:border-gray-700">
                        <img src="{{ $proofUrl }}"
                             alt="Bukti Pembayaran"
                             class="max-h-[350px] w-auto object-contain transition duration-500 group-hover:scale-105">
                        
                        <div class="absolute inset-0 flex items-center justify-center bg-gray-900/60 opacity-0 backdrop-blur-[2px] transition duration-300 group-hover:opacity-100">
                            <button type="button" 
                                    onclick="proof_modal.showModal()"
                                    class="bg-white/95 hover:bg-white flex items-center gap-2 rounded-full px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-gray-900 shadow-2xl transition-all transform translate-y-4 group-hover:translate-y-0 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                Lihat Ukuran Penuh
                            </button>
                        </div>
                    </div>

                    <!-- DaisyUI Modal -->
                    <dialog id="proof_modal" class="modal modal-bottom sm:modal-middle">
                        <div class="modal-box max-w-5xl p-2 bg-white/10 backdrop-blur-md shadow-none border-none overflow-hidden relative">
                            <button type="button" 
                                    onclick="proof_modal.close()"
                                    class="btn btn-sm btn-circle btn-ghost absolute right-4 top-4 z-10 text-white bg-black/40 hover:bg-black/60 border-none">✕</button>
                            <div class="flex items-center justify-center">
                                <img src="{{ $proofUrl }}" class="w-full h-auto rounded-lg shadow-2xl">
                            </div>
                        </div>
                        <div class="modal-backdrop backdrop-blur-sm bg-black/40">
                            <button type="button" onclick="proof_modal.close()" class="w-full h-full cursor-default">close</button>
                        </div>
                    </dialog>

                    <div class="mt-4 flex items-center gap-2 text-gray-400 dark:text-gray-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-[10px] italic">Tinjauan bukti pembayaran yang Anda unggah</p>
                    </div>
                @else
                    <div class="py-12 text-center">
                        <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gray-50/50 dark:bg-gray-900/30">
                            <svg class="h-10 w-10 text-gray-200 dark:text-gray-700"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.5"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Tidak ada file yang terdeteksi</p>
                        <p class="mt-1 text-[10px] text-gray-400/80">Silakan unggah bukti pembayaran di langkah sebelumnya</p>
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
