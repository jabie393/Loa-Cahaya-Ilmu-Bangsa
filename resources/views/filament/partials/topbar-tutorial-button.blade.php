<div x-data="{ 
    open: false, 
    videoUrl: 'https://www.youtube.com/embed/WWLTLepPDic?si=dzZyc4Qdj5Zr4iT9' 
}" class="flex items-center">
    <!-- Topbar Tutorial Button -->
    <button type="button" @click="open = true"
        class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-bold rounded-xl text-blue-700 dark:text-blue-300 bg-blue-50/70 dark:bg-blue-950/50 hover:bg-blue-100 dark:hover:bg-blue-900/70 border-2 border-blue-500 dark:border-blue-400 shadow-xs hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-blue-500/30"
        title="Buka Video Tutorial & Bantuan">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"
            class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
        </svg>
        <span>Tutorial</span>
    </button>

    <!-- Teleport Modal to Body -->
    <template x-teleport="body">
        <div x-show="open" x-cloak @keydown.escape.window="open = false"
            class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto p-3 sm:p-5 md:p-6"
            style="display: none;" role="dialog" aria-modal="true">
            <!-- Backdrop with high-performance blur -->
            <div x-show="open" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 backdrop-blur-none"
                x-transition:enter-end="opacity-100 backdrop-blur-md"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 backdrop-blur-md"
                x-transition:leave-end="opacity-0 backdrop-blur-none" @click="open = false"
                class="fixed inset-0 bg-slate-900/60 transition-opacity dark:bg-slate-950/80"></div>

            <!-- Modal Card Container -->
            <div x-show="open" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-6 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-6 scale-95"
                class="relative w-full max-w-[94vw] max-h-[94vh] flex flex-col transform overflow-hidden rounded-2xl bg-white/95 dark:bg-slate-900/95 text-left shadow-[0_25px_60px_-15px_rgba(59,130,246,0.22)] transition-all border border-blue-500/10 dark:border-blue-500/20 backdrop-blur-xl z-10 my-auto"
                style="max-width: min(1024px, calc((92vh - 130px) * 16 / 9));">
                <!-- Modal Header -->
                <div
                    class="relative z-10 flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 px-5 py-3 sm:px-6 sm:py-3.5 flex-shrink-0 bg-slate-50/60 dark:bg-slate-800/40">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 shadow-xs shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                            </svg>
                        </div>
                        <div>
                            <h3
                                class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white leading-tight">
                                Video Panduan &amp; Tutorial Penggunaan
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Pelajari fitur dan alur kerja sistem dengan video tutorial di bawah ini
                            </p>
                        </div>
                    </div>
                    <!-- Sleek Close Button with rotation -->
                    <button type="button" @click="open = false"
                        class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none dark:text-slate-500 dark:hover:bg-slate-800/80 dark:hover:text-slate-300 transition-all duration-300 transform hover:rotate-90"
                        aria-label="Tutup">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Video Frame Container (Spacious and wide 16:9) -->
                <div
                    class="relative z-10 flex-1 min-h-0 flex items-center justify-center p-3 sm:p-5 bg-black/5 dark:bg-black/30 overflow-hidden">
                    <div
                        class="relative w-full aspect-video rounded-xl overflow-hidden shadow-inner bg-black flex items-center justify-center mx-auto">
                        <!-- iframe src cleared when open is false to immediately stop audio/video -->
                        <iframe x-show="open" :src="open ? videoUrl : ''" title="Video Tutorial"
                            class="absolute inset-0 w-full h-full border-0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen></iframe>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div
                    class="relative z-10 flex items-center justify-between px-5 py-3 sm:px-6 sm:py-3.5 border-t border-slate-100 dark:border-slate-800/80 bg-slate-50/60 dark:bg-slate-800/40 text-xs text-slate-500 dark:text-slate-400 flex-shrink-0">
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0 1 18 0z" />
                        </svg>
                        <span>Tekan <kbd
                                class="px-1.5 py-0.5 font-mono text-[10px] bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded shadow-xs">ESC</kbd>
                            atau klik di luar</span>
                    </span>
                    <button type="button" @click="open = false"
                        class="px-4 py-2 font-bold rounded-xl text-xs text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition-all shadow-xs hover:scale-[1.02] active:scale-100">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>