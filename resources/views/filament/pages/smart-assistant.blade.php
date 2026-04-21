<x-filament-panels::page>
    <style>
        .assistant-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }

        .dark .assistant-card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .premium-gradient {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        }

        .drop-zone {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .drop-zone:hover {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.02);
            transform: translateY(-2px);
        }

        @keyframes pulse-soft {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .animate-pulse-soft {
            animation: pulse-soft 3s infinite;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-slide-up {
            animation: slideUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        }
    </style>

    <div class="space-y-8 animate-slide-up">
        <!-- Main Assistant Panel -->
        <div class="assistant-card rounded-3xl overflow-hidden">
            <div class="p-8 md:p-12">
                @if(session('error'))
                    <div class="mb-8 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl flex items-start gap-3">
                        <x-heroicon-o-exclamation-circle class="w-6 h-6 text-red-500" />
                        <div class="text-red-700 dark:text-red-400 text-sm font-medium pt-0.5">{{ session('error') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-8 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl">
                        <div class="flex items-start gap-3 mb-2">
                            <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-500" />
                            <span class="text-red-700 dark:text-red-400 font-bold">Terjadi Kesalahan:</span>
                        </div>
                        <ul class="list-disc pl-10 text-red-700 dark:text-red-400 text-sm space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('smart-assistant.review') }}" method="POST" enctype="multipart/form-data" id="reviewForm" onsubmit="showLoading()" class="max-w-3xl mx-auto">
                    @csrf

                    <div class="mb-10 group">
                        <div class="flex items-center justify-between mb-4">
                            <label class="block text-sm font-bold tracking-tight text-gray-700 dark:text-gray-300" for="document">
                                UNGGAH DOKUMEN JURNAL
                            </label>
                            <span class="text-xs font-medium text-gray-500 bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-full border border-gray-200 dark:border-gray-700">
                                PDF, DOCX (Max 10MB)
                            </span>
                        </div>
                        
                        <div class="relative">
                            <label for="document" class="drop-zone flex flex-col items-center justify-center w-full h-64 border-2 border-gray-200 dark:border-gray-700 border-dashed rounded-[2rem] cursor-pointer bg-white/50 dark:bg-gray-900/50 shadow-sm">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4">
                                    <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/30 rounded-2xl text-blue-600 dark:text-blue-400 group-hover:animate-pulse-soft transition-all">
                                        <x-heroicon-o-document-arrow-up class="w-12 h-12" />
                                    </div>
                                    <p class="mb-2 text-lg font-bold text-gray-800 dark:text-gray-200">
                                        Seret file atau klik untuk memilih
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Dokumen Anda akan segera diproses oleh AI kami
                                    </p>
                                </div>
                                <input id="document" name="document" type="file" class="hidden" accept=".pdf,.docx,.doc" onchange="updateFileName(this)" required />
                            </label>
                        </div>

                        <div id="fileNameDisplay" class="mt-6 p-4 rounded-2xl bg-blue-50/50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 hidden flex items-center justify-between group/file">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-600 rounded-xl text-white shadow-lg shadow-blue-500/30">
                                    <x-heroicon-o-document-text class="w-6 h-6" />
                                </div>
                                <div>
                                    <p id="fileNameText" class="text-sm font-bold text-gray-800 dark:text-gray-200 truncate max-w-[200px] md:max-w-md"></p>
                                    <p class="text-[10px] uppercase tracking-widest text-blue-600 dark:text-blue-400 font-bold">Siap Dianalisis</p>
                                </div>
                            </div>
                            <button type="button" onclick="resetFile()" class="p-2 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg text-gray-400 hover:text-red-500 transition-colors">
                                <x-heroicon-o-x-mark class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <button type="submit" id="submitBtn" class="premium-gradient text-white font-extrabold text-lg px-10 py-4 rounded-2xl shadow-xl shadow-blue-500/25 hover:shadow-blue-500/40 hover:-translate-y-1 transition-all flex items-center gap-3 group">
                            <x-heroicon-o-sparkles class="w-6 h-6 group-hover:rotate-12 transition-transform" />
                            Mulai Reviu AI
                        </button>
                    </div>
                </form>

                <!-- Loading State -->
                <div id="loadingState" class="hidden text-center py-20">
                    <div class="inline-flex relative mb-8">
                        <div class="w-24 h-24 border-4 border-blue-100 dark:border-blue-900 rounded-full"></div>
                        <div class="absolute top-0 left-0 w-24 h-24 border-4 border-blue-600 rounded-full border-t-transparent animate-spin"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <x-heroicon-o-sparkles class="w-10 h-10 text-blue-600 animate-pulse" />
                        </div>
                    </div>
                    <h3 class="text-2xl font-black tracking-tight mb-3">AI Sedang Bekerja...</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto leading-relaxed">
                        Kami sedang menganalisis naskah Anda secara mendalam. Ini biasanya memakan waktu sekitar <span class="font-bold text-blue-600">10-20 detik</span>.
                    </p>
                </div>
            </div>
        </div>

        <!-- Review Result -->
        @if(session('review'))
            <div class="assistant-card rounded-3xl overflow-hidden border-t-4 border-t-blue-600" id="resultSection">
                <div class="p-8 md:p-12">
                    <div class="flex items-center gap-4 mb-10">
                        <div class="p-3 bg-blue-600 rounded-2xl text-white shadow-xl shadow-blue-500/30">
                            <x-heroicon-o-check-badge class="w-8 h-8" />
                        </div>
                        <div>
                            <h2 class="text-2xl font-black tracking-tight">Hasil Reviu Assistant</h2>
                            <p class="text-sm text-gray-500">Analisis otomatis berdasarkan standar publikasi jurnal.</p>
                        </div>
                        <button onclick="window.print()" class="ml-auto p-3 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl transition-colors hidden md:block" title="Cetak Hasil">
                            <x-heroicon-o-printer class="w-6 h-6 text-gray-600 dark:text-gray-400" />
                        </button>
                    </div>

                    <div class="prose prose-slate dark:prose-invert max-w-none prose-headings:font-black prose-p:leading-relaxed prose-li:my-1">
                        {!! str(session('review'))->markdown() !!}
                    </div>

                    <div class="mt-12 p-6 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 flex items-center gap-4">
                        <div class="p-2 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg text-yellow-600 dark:text-yellow-400">
                            <x-heroicon-o-information-circle class="w-5 h-5" />
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 italic">
                            Disclaimer: Hasil analisis ini dihasilkan secara otomatis oleh kecerdasan buatan. Kami menyarankan untuk tetap berkonsultasi dengan reviewer ahli untuk akurasi maksimal.
                        </p>
                    </div>
                </div>
            </div>
            <script>
                setTimeout(() => {
                    document.getElementById('resultSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 500);
            </script>
        @endif
    </div>

    <script>
        function updateFileName(input) {
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            const fileNameText = document.getElementById('fileNameText');
            if (input.files && input.files.length > 0) {
                fileNameText.textContent = input.files[0].name;
                fileNameDisplay.classList.remove('hidden');
                document.querySelector('.drop-zone').classList.add('hidden');
            } else {
                resetFile();
            }
        }

        function resetFile() {
            const input = document.getElementById('document');
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            input.value = '';
            fileNameDisplay.classList.add('hidden');
            document.querySelector('.drop-zone').classList.remove('hidden');
        }

        function showLoading() {
            document.getElementById('reviewForm').classList.add('hidden');
            document.getElementById('loadingState').classList.remove('hidden');
            document.getElementById('loadingState').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    </script>
</x-filament-panels::page>