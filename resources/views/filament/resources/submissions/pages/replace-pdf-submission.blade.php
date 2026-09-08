<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Top Header -->
        <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 rounded-xl border border-blue-100 dark:border-blue-900/50">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Layanan Ganti PDF Naskah</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Unggah file artikel ilmiah yang telah diperbarui untuk disinkronkan ke sistem OJS.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left Card: Current Manuscript & Author Metadata -->
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm space-y-5">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <span>Data Naskah Saat Ini</span>
                        </h3>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                            <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                            OJS: Submitted
                        </span>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <span class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold block mb-1">Judul Artikel:</span>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white leading-relaxed">
                                {{ $record->title ?: 'Naskah #' . $record->id }}
                            </h4>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                            <div>
                                <span class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold block mb-0.5">Jurnal Tujuan:</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $record->journal?->name ?? 'Jurnal CIB' }}</span>
                            </div>
                            <div>
                                <span class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold block mb-0.5">Tipe Jurnal:</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $record->isExternal() ? 'Internasional' : 'Nasional' }}</span>
                            </div>
                        </div>

                        @php
                            $authors = is_array($record->authors) ? $record->authors : [];
                            $authorCount = count($authors);
                            if ($authorCount === 0 && !empty($record->author_name)) {
                                $authors = [['name' => $record->author_name]];
                                $authorCount = 1;
                            }
                        @endphp

                        <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold">Daftar Penulis Terdaftar:</span>
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                    {{ $authorCount }} Penulis
                                </span>
                            </div>
                            <div class="space-y-1.5 bg-gray-50 dark:bg-gray-800/60 p-3 rounded-xl border border-gray-100 dark:border-gray-800">
                                @forelse($authors as $idx => $auth)
                                    <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                        <span class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 font-bold text-[10px] flex items-center justify-center shrink-0">
                                            {{ $idx + 1 }}
                                        </span>
                                        <span class="font-semibold">{{ is_array($auth) ? ($auth['name'] ?? '-') : $auth }}</span>
                                    </div>
                                @empty
                                    <span class="text-gray-400 italic">Belum ada data author spesifik.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fee Card -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/20 rounded-2xl border border-blue-200 dark:border-blue-800/60 p-5 text-xs text-blue-900 dark:text-blue-200 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <div>
                                <span class="block text-[10px] uppercase tracking-wider text-blue-600 dark:text-blue-400 font-bold">Biaya Layanan</span>
                                <span class="font-extrabold text-sm text-blue-950 dark:text-white">Ganti PDF Naskah</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xl font-black text-blue-700 dark:text-blue-300 font-mono">Rp 25.000</span>
                        </div>
                    </div>
                    <p class="text-[11px] text-blue-700/80 dark:text-blue-300/80 leading-relaxed border-t border-blue-200/60 dark:border-blue-800/40 pt-2.5">
                        Biaya mencakup verifikasi berkas naskah terbaru, pembaruan file pada server publikasi, dan sinkronisasi otomatis ke OJS (Open Journal Systems).
                    </p>
                </div>
            </div>

            <!-- Right Card: Standard Native Filament Form & Submit -->
            <div class="lg:col-span-7">
                <form wire:submit.prevent="submit" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 shadow-sm space-y-6">
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-4">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Unggah Berkas PDF Baru</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pilih atau unggah dokumen PDF hasil revisi Anda sesuai dengan template jurnal.</p>
                    </div>

                    <!-- Notice Alert -->
                    <div class="p-4 bg-blue-50/60 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800/60 rounded-xl flex items-start gap-3 text-xs text-blue-900 dark:text-blue-200 leading-relaxed">
                        <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                        <div>
                            <strong class="font-bold">Ketentuan Penggantian PDF:</strong>
                            <p class="mt-0.5 text-blue-800 dark:text-blue-300">
                                Pastikan berkas PDF yang diunggah <strong>sesuai dengan template jurnal</strong> dan <strong>jumlah penulis tetap sama</strong> dengan naskah awal ({{ $authorCount }} orang). Penambahan atau pengurangan author tidak diizinkan melalui layanan ini.
                            </p>
                        </div>
                    </div>

                    <!-- Standard Filament FileUpload Component -->
                    <div>
                        {{ $this->form }}
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            wire:target="submit"
                            class="inline-flex items-center justify-center gap-2.5 px-4 py-2 h-9 whitespace-nowrap bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-xs rounded-lg shadow-sm hover:shadow transition-all disabled:opacity-75 disabled:cursor-wait cursor-pointer">
                            
                            <!-- Credit Card Icon (Idle) -->
                            <svg wire:loading.remove wire:target="submit" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-6-9.75h16.5a1.5 1.5 0 0 1 1.5 1.5v10.5a1.5 1.5 0 0 1-1.5 1.5H3.75A1.5 1.5 0 0 1 2.25 18V7.5a1.5 1.5 0 0 1 1.5-1.5Z" />
                            </svg>

                            <!-- Circular Spinner Icon (Loading) -->
                            <svg wire:loading wire:target="submit" class="w-4 h-4 animate-spin text-white shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3.5"></circle>
                                <path class="opacity-100" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>

                            <!-- Button Label -->
                            <span class="whitespace-nowrap font-bold">Proceed to Payment</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-filament-panels::page>
