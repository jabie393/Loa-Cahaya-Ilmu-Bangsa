<!-- Maximize Trigger (Tab Ikon di pinggir kanan) -->
<div id="mascot-maximize-trigger" onclick="Mascot.toggleMinimize()"
    style="background-color: #003da3;"
    class="fixed bottom-10 right-0 z-[9999] hidden cursor-pointer items-center justify-center rounded-l-full p-2 pr-1 text-white shadow-2xl backdrop-blur-md transition-all duration-300 hover:pr-3 group">
    <span class="material-symbols-outlined !text-[20px] transition-transform group-hover:scale-110">smart_toy</span>
</div>

<div id="mascot-container"
    class="fixed bottom-6 right-6 z-[9999] flex flex-col items-end gap-3 transition-all duration-500 ease-in-out"
    data-minimized="false">
    <!-- Chat Bubble -->
    <div id="mascot-bubble"
        class="hidden max-w-[250px] transform rounded-2xl bg-white/80 p-4 text-sm font-medium text-slate-800 shadow-lg backdrop-blur-md transition-all duration-300 md:text-base">
        <div class="relative">
            <span id="mascot-message">Halo! Saya Kanda Putra. Ada yang bisa saya bantu?</span>
            <button onclick="Mascot.hideBubble()"
                class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-slate-200 text-slate-500 hover:bg-slate-300">
                <span class="material-symbols-outlined !text-[14px]">close</span>
            </button>
        </div>
        <!-- Bubble Arrow -->
        <div class="absolute -bottom-2 right-6 h-4 w-4 rotate-45 bg-white/80 backdrop-blur-md"></div>
    </div>

    <!-- Mascot Wrapper -->
    <div class="group relative flex items-end">
        <!-- Action Buttons (Visible on hover/click) -->
        <div id="mascot-actions"
            class="absolute -left-12 bottom-0 flex flex-col gap-2 opacity-0 transition-opacity group-hover:opacity-100">
            <button onclick="Mascot.toggleMinimize()" title="Minimize"
                class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-md transition-transform hover:scale-110">
                <span class="material-symbols-outlined text-slate-600" id="minimize-icon">collapse_all</span>
            </button>
        </div>

        <!-- Mascot Image -->
        <div id="mascot-avatar" onclick="Mascot.togglePanel()"
            class="relative cursor-pointer transition-transform duration-300 hover:scale-105 active:scale-95">
            <img id="mascot-img" src="{{ asset('assets/mascot/idle.png') }}" alt="Kanda Putra - Maskot Helper"
                class="h-[70px] w-auto object-contain drop-shadow-xl animate-float md:h-[100px]"
                onerror="this.src='https://ui-avatars.com/api/?name=KP&background=0D8ABC&color=fff'">

            <!-- Notification Badge (for errors/success) -->
            <div id="mascot-badge" class="absolute right-0 top-0 hidden h-4 w-4 animate-ping rounded-full bg-red-500">
            </div>
        </div>
    </div>

    <!-- Help Panel -->
    <div id="mascot-panel"
        class="hidden w-[280px] overflow-hidden rounded-3xl bg-white shadow-2xl transition-all duration-300 md:w-[320px]">
        <div class="bg-primary p-6 text-white">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">Kanda Putra</h3>
                <button onclick="Mascot.togglePanel()" class="text-white/80 hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <p class="mt-1 text-xs text-white/80">Kami siap membantu proses LOA Anda</p>
        </div>

        <div class="max-h-[350px] overflow-y-auto p-4">
            <div class="mb-4">
                <h4 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Tips Cepat</h4>
                <div class="space-y-2">
                    <div class="flex items-start gap-3 rounded-xl bg-slate-50 p-3 text-sm">
                        <span class="material-symbols-outlined text-primary !text-xl">description</span>
                        <span>Gunakan format PDF/DOCX untuk semua dokumen pengajuan.</span>
                    </div>
                    <div class="flex items-start gap-3 rounded-xl bg-slate-50 p-3 text-sm">
                        <span class="material-symbols-outlined text-primary !text-xl">check_circle</span>
                        <span>Pastikan bukti pembayaran terlihat jelas dan tidak blur.</span>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h4 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">FAQ Singkat</h4>
                <details class="group border-b border-slate-100 py-2">
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between text-sm font-semibold text-slate-700">
                        Berapa lama proses LOA?
                        <span
                            class="material-symbols-outlined transition-transform group-open:rotate-180">expand_more</span>
                    </summary>
                    <p class="mt-2 text-xs leading-relaxed text-slate-500">Normalnya 1-3 hari kerja setelah verifikasi
                        pembayaran berhasil.</p>
                </details>
                <details class="group border-b border-slate-100 py-2">
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between text-sm font-semibold text-slate-700">
                        Bagaimana jika data salah?
                        <span
                            class="material-symbols-outlined transition-transform group-open:rotate-180">expand_more</span>
                    </summary>
                    <p class="mt-2 text-xs leading-relaxed text-slate-500">Anda akan menerima notifikasi revisi. Silakan
                        periksa dashboard Anda.</p>
                </details>
                <details class="group border-b border-slate-100 py-2">
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between text-sm font-semibold text-slate-700">
                        Ketika kuota Review dan Plagiarism habis bagaimana?
                        <span
                            class="material-symbols-outlined transition-transform group-open:rotate-180">expand_more</span>
                    </summary>
                    <p class="mt-2 text-xs leading-relaxed text-slate-500">Anda dapat membeli credits tambahan dengan
                        cara menghubungi admin.</p>
                </details>
            </div>

            @php
                $adminPhone = \App\Models\User::role('super_admin')->orderBy('id')->first()?->phone ?? '628123456789';
                // Bersihkan karakter non-numerik jika ada
                $adminPhone = preg_replace('/[^0-9]/', '', $adminPhone);
                // Pastikan format diawali dengan kode negara jika belum
                if (strpos($adminPhone, '0') === 0) {
                    $adminPhone = '62' . substr($adminPhone, 1);
                }
            @endphp
            <a href="https://wa.me/{{ $adminPhone }}" target="_blank"
                class="flex w-full items-center justify-center gap-2 rounded-2xl bg-green-500 py-3 font-bold text-white shadow-lg transition-transform hover:scale-[1.02] active:scale-95">
                <span class="material-symbols-outlined">support_agent</span>
                Hubungi Admin (WA)
            </a>
        </div>
    </div>
</div>

@if($errors->any())
    <script>
        (function () {
            const trigger = () => {
                setTimeout(() => {
                    if (window.Mascot) {
                        Mascot.setPose('warning');
                        Mascot.setMessage('Ups! Masih ada data yang belum lengkap. Periksa kembali form Anda.');
                    }
                }, 2500);
            };
            if (document.readyState === 'loading') {
                window.addEventListener('DOMContentLoaded', trigger);
            } else {
                trigger();
            }
        })();
    </script>
@endif

@if(session('success'))
    <script>
        (function () {
            const trigger = () => {
                setTimeout(() => {
                    if (window.Mascot) {
                        Mascot.setPose('success');
                        Mascot.setMessage('Berhasil! Pengajuan Anda telah kami terima.');
                    }
                }, 2000);
            };
            if (document.readyState === 'loading') {
                window.addEventListener('DOMContentLoaded', trigger);
            } else {
                trigger();
            }
        })();
    </script>
@endif