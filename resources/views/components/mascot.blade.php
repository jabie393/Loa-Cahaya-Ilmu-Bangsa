<!-- Maximize Trigger (Tab Ikon di pinggir kanan) -->
<style>
    /* Force flex display when panel is open to bypass browser caching of old mascot.js */
    #mascot-panel:not(.hidden) {
        display: flex !important;
    }
</style>
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
                style="image-rendering: -webkit-optimize-contrast; image-rendering: crisp-edges; transform: translate3d(0,0,0); filter: drop-shadow(0 10px 10px rgba(0,0,0,0.15));"
                class="h-[70px] w-auto object-contain animate-float md:h-[100px]"
                onerror="this.src='https://ui-avatars.com/api/?name=KP&background=0D8ABC&color=fff'">

            <!-- Notification Badge (for errors/success) -->
            <div id="mascot-badge" class="absolute right-0 top-0 hidden h-4 w-4 animate-ping rounded-full bg-red-500">
            </div>
        </div>
    </div>

    <!-- Help Panel (Chatbot Interface) -->
    <div id="mascot-panel"
        class="hidden absolute bottom-full mb-4 right-0 flex-col w-[320px] h-[550px] max-h-[75vh] overflow-hidden rounded-3xl bg-white shadow-2xl transition-all duration-300 md:w-[380px] z-[10000]">
        
        <!-- Header -->
        <div class="bg-primary p-4 text-white flex-shrink-0 relative">
            <!-- Background Decoration -->
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10 blur-xl"></div>
            <div class="absolute -bottom-8 -left-8 h-32 w-32 rounded-full bg-black/5 blur-2xl"></div>
            
            <div class="flex items-center justify-between relative z-10">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 overflow-hidden rounded-full bg-white/20 p-1 border border-white/30 shadow-inner">
                        <img src="{{ asset('assets/mascot/idle.png') }}" class="h-full w-full object-contain" id="chatbot-header-img" alt="Kanda Putra">
                    </div>
                    <div>
                        <h3 class="text-base font-bold leading-none tracking-wide drop-shadow-sm">Kanda Putra</h3>
                        <p class="mt-1 flex items-center gap-1 text-[11px] text-white/90">
                            <span class="relative flex h-2 w-2">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-2 w-2 bg-green-400"></span>
                            </span>
                            AI Assistant
                        </p>
                    </div>
                </div>
                <button onclick="Mascot.togglePanel()" class="flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-white/90 backdrop-blur-sm transition-all hover:bg-white/20 hover:text-white">
                    <span class="material-symbols-outlined !text-[18px]">close</span>
                </button>
            </div>
        </div>

        <!-- Messages Area -->
        <div id="chatbot-messages" style="min-height: 0;" class="flex-1 min-h-0 overflow-y-auto p-4 bg-[#f8fafc] flex flex-col gap-4 scroll-smooth">
            <!-- Welcome Message -->
            <div class="flex gap-3">
                <div class="h-8 w-8 flex-shrink-0 rounded-full bg-white p-1 shadow-sm border border-slate-100">
                    <img src="{{ asset('assets/mascot/idle.png') }}" class="h-full w-full object-contain">
                </div>
                <div class="flex flex-col gap-1 max-w-[85%]">
                    <div class="rounded-2xl rounded-tl-none bg-white p-3.5 text-sm leading-relaxed text-slate-700 shadow-sm border border-slate-100">
                        Halo! Saya <b>Kanda Putra</b>. Ada yang bisa saya bantu terkait pengajuan LOA Anda? Anda bisa memilih pertanyaan di bawah ini atau mengetik langsung pertanyaan Anda. ✨
                    </div>
                </div>
            </div>

            <!-- Dynamic FAQ Suggestions (Loaded via JS) -->
            <div id="chatbot-faq-suggestions" class="flex flex-wrap gap-2 pl-11">
                <!-- FAQ buttons injected here -->
            </div>
        </div>

        <!-- Typing Indicator -->
        <div id="chatbot-typing" class="hidden px-4 pb-3 pt-1 bg-[#f8fafc]">
            <div class="flex gap-3">
                <div class="h-8 w-8 flex-shrink-0 rounded-full bg-white p-1 shadow-sm border border-slate-100">
                    <img src="{{ asset('assets/mascot/thinking.png') }}" class="h-full w-full object-contain">
                </div>
                <div class="flex items-center gap-1.5 rounded-2xl rounded-tl-none bg-white px-4 py-3 shadow-sm border border-slate-100 w-fit">
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-slate-400 [animation-delay:-0.3s]"></span>
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-slate-400 [animation-delay:-0.15s]"></span>
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-slate-400"></span>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 bg-white border-t border-slate-100 flex-shrink-0 shadow-[0_-4px_10px_rgba(0,0,0,0.02)]">
            <form id="chatbot-form" class="flex items-end gap-2 relative" onsubmit="Mascot.sendMessage(event)">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" id="chatbot-csrf">
                <textarea id="chatbot-input" rows="1" class="w-full resize-none rounded-2xl border-0 bg-slate-100 py-3.5 pl-4 pr-12 text-sm text-slate-700 focus:bg-slate-50 focus:ring-2 focus:ring-primary/20 transition-all shadow-inner" placeholder="Ketik pesan..." onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); Mascot.sendMessage(event); }"></textarea>
                <button type="submit" class="absolute right-1.5 bottom-1.5 flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-white shadow-md transition-all hover:scale-105 hover:shadow-lg active:scale-95 disabled:opacity-50 disabled:hover:scale-100 disabled:shadow-none" id="chatbot-submit">
                    <span class="material-symbols-outlined !text-[20px] ml-0.5">send</span>
                </button>
            </form>
            @php
                $adminPhone = \App\Models\User::role('super_admin')->orderBy('id')->first()?->phone ?? '628123456789';
                $adminPhone = preg_replace('/[^0-9]/', '', $adminPhone);
                if (strpos($adminPhone, '0') === 0) {
                    $adminPhone = '62' . substr($adminPhone, 1);
                }
            @endphp
            <div class="mt-2.5 flex items-center justify-center gap-1.5 text-[11px] text-slate-500">
                <span class="material-symbols-outlined !text-[14px]">support_agent</span>
                Masalah mendesak? 
                <a href="https://wa.me/{{ $adminPhone }}" target="_blank" class="text-primary font-semibold hover:text-primary-dark hover:underline transition-colors">
                    Hubungi Admin
                </a>
            </div>
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