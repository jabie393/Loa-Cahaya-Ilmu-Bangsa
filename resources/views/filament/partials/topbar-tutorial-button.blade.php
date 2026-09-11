<div x-data="{ 
    open: false, 
    videoUrl: 'https://www.youtube.com/embed/uV9koQm__fI?autoplay=1&rel=0' 
}" class="flex items-center">
    <!-- Topbar Tutorial Button -->
    <button 
        type="button" 
        @click="open = true" 
        class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-bold rounded-xl text-blue-700 dark:text-blue-300 bg-blue-50/70 dark:bg-blue-950/50 hover:bg-blue-100 dark:hover:bg-blue-900/70 border-2 border-blue-500 dark:border-blue-400 shadow-xs hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-blue-500/30"
        title="Buka Video Tutorial & Bantuan"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
        </svg>
        <span>Tutorial</span>
    </button>

    <!-- Teleport Modal to Body -->
    <template x-teleport="body">
        <div 
            x-show="open" 
            x-cloak
            @keydown.escape.window="open = false"
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
            style="display: none;"
        >
            <!-- Backdrop -->
            <div 
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="open = false" 
                class="fixed inset-0 bg-gray-950/75 backdrop-blur-xs transition-opacity"
            ></div>

            <!-- Modal Dialog -->
            <div 
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-4xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden z-10 my-auto"
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/40">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white leading-none">
                                Video Panduan &amp; Tutorial Penggunaan
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Pelajari fitur dan alur kerja sistem dengan video tutorial di bawah ini
                            </p>
                        </div>
                    </div>
                    <!-- Close Button -->
                    <button 
                        type="button" 
                        @click="open = false" 
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors focus:outline-none"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Video Frame Container (16:9 Aspect Ratio) -->
                <div class="p-4 sm:p-5 bg-black/5 dark:bg-black/30">
                    <div class="relative w-full aspect-video rounded-xl overflow-hidden shadow-inner bg-black">
                        <!-- iframe src cleared when open is false to immediately stop audio/video -->
                        <iframe 
                            x-show="open"
                            :src="open ? videoUrl : ''" 
                            title="Video Tutorial" 
                            class="absolute inset-0 w-full h-full border-0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-between px-5 py-3 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/40 text-xs text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Tekan tombol <kbd class="px-1.5 py-0.5 font-mono text-[10px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-xs">ESC</kbd> atau klik di luar untuk menutup
                    </span>
                    <button 
                        type="button" 
                        @click="open = false" 
                        class="px-3 py-1.5 font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 transition-colors shadow-xs"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
