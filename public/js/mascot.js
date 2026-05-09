/**
 * Mascot Helper & Chatbot JavaScript Logic
 * Handling poses, messages, and AI chatbot interactivity.
 */

if (!window.Mascot) {
    window.Mascot = {
        state: {
            minimized: localStorage.getItem("mascot_minimized") === "true",
            pose: "idle",
            isTyping: false,
            panelOpen: false,
            assetPath: "/assets/mascot/",
            sessionId: localStorage.getItem("mascot_session_id") || null,
        },

        init() {
            if (!this.state.sessionId) {
                this.state.sessionId = 'sess_' + Math.random().toString(36).substr(2, 9) + Date.now();
                localStorage.setItem("mascot_session_id", this.state.sessionId);
            }

            this.dom = {
                container: document.getElementById("mascot-container"),
                img: document.getElementById("mascot-img"),
                bubble: document.getElementById("mascot-bubble"),
                message: document.getElementById("mascot-message"),
                panel: document.getElementById("mascot-panel"),
                minimizeIcon: document.getElementById("minimize-icon"),
                badge: document.getElementById("mascot-badge"),
                maximizeTrigger: document.getElementById("mascot-maximize-trigger"),
                // Chatbot specific
                chatMessages: document.getElementById("chatbot-messages"),
                chatInput: document.getElementById("chatbot-input"),
                chatForm: document.getElementById("chatbot-form"),
                chatSubmitBtn: document.getElementById("chatbot-submit"),
                typingIndicator: document.getElementById("chatbot-typing"),
                faqSuggestions: document.getElementById("chatbot-faq-suggestions"),
                csrfToken: document.getElementById("chatbot-csrf") ? document.getElementById("chatbot-csrf").value : '',
            };

            if (!this.dom.container) return;

            // Prevent double init
            if (this.dom.container.dataset.initialized) {
                return;
            }
            this.dom.container.dataset.initialized = "true";

            // Apply initial state
            if (this.state.minimized) {
                this.dom.container.classList.add("hidden");
                this.dom.maximizeTrigger.classList.remove("hidden");
                this.dom.maximizeTrigger.classList.add("flex");
            }

            // Set up listeners
            this.setupListeners();

            // Initial Greeting based on page
            setTimeout(() => this.handleAutoMessage(), 2500);
            
            // Load FAQs
            this.loadFaqs();
        },

        setupListeners() {
            this.dom.img.addEventListener("mouseenter", () => {
                if (this.state.pose === "idle" && !this.state.isTyping) this.setPose("happy");
            });

            this.dom.img.addEventListener("mouseleave", () => {
                if (this.state.pose === "happy" && !this.state.isTyping) this.setPose("idle");
            });

            if (this.dom.chatInput) {
                this.dom.chatInput.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                    if (this.value.trim() !== '') {
                        Mascot.dom.chatSubmitBtn.removeAttribute('disabled');
                    } else {
                        Mascot.dom.chatSubmitBtn.setAttribute('disabled', 'true');
                    }
                });
            }
        },

        setPose(pose) {
            if (this.state.pose === pose) return;

            this.dom.img.classList.add("pose-changing");

            setTimeout(() => {
                this.state.pose = pose;
                this.dom.img.src = `${this.state.assetPath}${pose}.png`;
                this.dom.img.classList.remove("pose-changing");

                if (pose === "warning" || pose === "success") {
                    this.dom.badge.classList.remove("hidden");
                    if (pose === "warning") this.dom.badge.classList.replace("bg-green-500", "bg-red-500");
                    if (pose === "success") this.dom.badge.classList.add("bg-green-500");
                } else {
                    this.dom.badge.classList.add("hidden");
                }
            }, 150);
        },

        setMessage(text, duration = 6000) {
            if (this.state.minimized || this.state.panelOpen) return;

            this.dom.message.innerText = text;
            this.dom.bubble.classList.remove("hidden");
            this.dom.bubble.classList.add("animate-reveal");

            if (duration > 0) {
                setTimeout(() => this.hideBubble(), duration);
            }
        },

        hideBubble() {
            this.dom.bubble.classList.add("hidden");
        },

        toggleMinimize() {
            this.state.minimized = !this.state.minimized;
            localStorage.setItem("mascot_minimized", this.state.minimized);

            if (this.state.minimized) {
                this.dom.container.classList.add("hidden");
                this.dom.maximizeTrigger.classList.remove("hidden");
                this.dom.maximizeTrigger.classList.add("flex");
                this.hideBubble();
                this.dom.panel.classList.add("hidden");
                this.dom.panel.classList.remove("flex");
            } else {
                this.dom.container.classList.remove("hidden");
                this.dom.maximizeTrigger.classList.add("hidden");
                this.dom.maximizeTrigger.classList.remove("flex");
                this.handleAutoMessage();
            }
        },

        togglePanel() {
            if (this.state.minimized) {
                this.toggleMinimize();
                return;
            }

            this.state.panelOpen = !this.state.panelOpen;
            if (this.state.panelOpen) {
                this.dom.panel.classList.remove("hidden");
                this.dom.panel.classList.add("flex");
                this.hideBubble();
                this.scrollToBottom();
            } else {
                this.dom.panel.classList.add("hidden");
                this.dom.panel.classList.remove("flex");
            }
        },

        handleAutoMessage() {
            const path = window.location.pathname;
            let message = "Halo! Saya Kanda Putra, asisten LOA Anda. 👋";
            let pose = "idle";

            if (path.includes("submissions/create")) {
                this.dom.container.style.bottom = "80px";
            } else {
                this.dom.container.style.bottom = "24px";
            }

            if (path === "/" || path === "/index.php") {
                message = "Halo! Saya Kanda Putra. Butuh bantuan untuk pengajuan LOA? Klik saya ya! ✨";
                pose = "happy";
            } else if (path.includes("journal")) {
                message = "Silakan pilih jurnal tujuan Anda untuk melakukan pengajuan LOA. 📚";
                pose = "happy";
            } else if (path.includes("submissions/create")) {
                message = "Pastikan semua data sudah benar dan file PDF sesuai format ya. 📄";
                pose = "thinking";
            } else if (path.includes("submissions") && !path.includes("create") && !path.includes("edit")) {
                message = "Anda dapat memantau status pengajuan LOA Anda di sini secara real-time. 🔍";
                pose = "idle";
            } else if (path.includes("edit")) {
                message = "Silakan periksa catatan dari admin jika ada revisi yang diminta. ✍️";
                pose = "thinking";
            } else if (path.includes("pre-submission-reviews")) {
                message = "Selamat datang di Review Pra-OJS! Unggah naskah Anda dan biarkan reviewer kami menganalisis kualitasnya. ✨";
                pose = "happy";
            } else if (path.includes("plagiarism")) {
                message = "Layanan cek plagiarisme akan membantu validasi naskah Anda. 🛡️";
                pose = "thinking";
            }

            this.setPose(pose);
            this.setMessage(message);
        },

        // --- Chatbot Specific Methods ---

        async loadFaqs() {
            if (!this.dom.faqSuggestions) return;
            
            try {
                const response = await fetch('/chatbot/faqs');
                const data = await response.json();
                
                if (data.success && data.data.length > 0) {
                    this.dom.faqSuggestions.innerHTML = '';
                    data.data.forEach(faq => {
                        const btn = document.createElement('button');
                        btn.className = "rounded-full border border-primary/30 bg-primary/5 px-3 py-1.5 text-xs font-medium text-primary transition-colors hover:bg-primary hover:text-white";
                        btn.innerText = faq.question;
                        btn.onclick = () => this.sendDirectMessage(faq.question);
                        this.dom.faqSuggestions.appendChild(btn);
                    });
                }
            } catch (error) {
                console.error("Failed to load FAQs", error);
            }
        },

        sendDirectMessage(text) {
            this.dom.chatInput.value = text;
            this.dom.chatSubmitBtn.removeAttribute('disabled');
            this.sendMessage(new Event('submit'));
        },

        appendUserMessage(text) {
            const html = `
            <div class="flex gap-3 flex-row-reverse">
                <div class="h-8 w-8 flex-shrink-0 rounded-full bg-primary flex items-center justify-center shadow-sm">
                    <span class="material-symbols-outlined text-white !text-[20px]">person</span>
                </div>
                <div class="flex flex-col gap-1 max-w-[85%] items-end">
                    <div class="rounded-2xl rounded-tr-none bg-primary p-3.5 text-sm leading-relaxed text-white shadow-sm">
                        ${this.escapeHtml(text)}
                    </div>
                </div>
            </div>`;
            this.dom.chatMessages.insertAdjacentHTML('beforeend', html);
            this.scrollToBottom();
        },

        appendBotMessage(text, source = 'gemini') {
            // Convert simple markdown (bold)
            let formattedText = this.escapeHtml(text)
                .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>')
                .replace(/\*(.*?)\*/g, '<i>$1</i>')
                .replace(/\n/g, '<br>');

            const sourceIcon = source === 'faq' ? 'bolt' : 'auto_awesome';
            const sourceColor = source === 'faq' ? 'text-amber-500' : 'text-purple-500';

            const html = `
            <div class="flex gap-3">
                <div class="h-8 w-8 flex-shrink-0 rounded-full bg-white p-1 shadow-sm border border-slate-100">
                    <img src="${this.state.assetPath}idle.png" class="h-full w-full object-contain">
                </div>
                <div class="flex flex-col gap-1 max-w-[85%]">
                    <div class="rounded-2xl rounded-tl-none bg-white p-3.5 text-sm leading-relaxed text-slate-700 shadow-sm border border-slate-100">
                        ${formattedText}
                    </div>
                    <div class="flex items-center gap-1 pl-1 text-[10px] text-slate-400">
                        <span class="material-symbols-outlined !text-[12px] ${sourceColor}">${sourceIcon}</span>
                        ${source === 'faq' ? 'FAQ Database' : 'Gemini AI'}
                    </div>
                </div>
            </div>`;
            this.dom.chatMessages.insertAdjacentHTML('beforeend', html);
            this.scrollToBottom();
        },

        scrollToBottom() {
            setTimeout(() => {
                if (this.dom.chatMessages) {
                    this.dom.chatMessages.scrollTop = this.dom.chatMessages.scrollHeight;
                }
            }, 50);
        },

        async sendMessage(event) {
            if (event) event.preventDefault();

            const message = this.dom.chatInput.value.trim();
            if (!message || this.state.isTyping) return;

            // Clear input
            this.dom.chatInput.value = '';
            this.dom.chatInput.style.height = 'auto';
            this.dom.chatSubmitBtn.setAttribute('disabled', 'true');

            // Hide suggestions after first message
            if (this.dom.faqSuggestions) {
                this.dom.faqSuggestions.style.display = 'none';
            }

            this.appendUserMessage(message);
            
            // Set Typing state
            this.state.isTyping = true;
            this.dom.typingIndicator.classList.remove('hidden');
            this.setPose("thinking");
            this.scrollToBottom();

            // Prepare context
            const context = {
                'Current URL': window.location.href,
                'Page Path': window.location.pathname
            };

            try {
                const response = await fetch('/chatbot/message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.dom.csrfToken
                    },
                    body: JSON.stringify({
                        message: message,
                        session_id: this.state.sessionId,
                        context: context
                    })
                });

                const data = await response.json();
                
                this.state.isTyping = false;
                this.dom.typingIndicator.classList.add('hidden');
                this.setPose("idle");

                if (data.success) {
                    this.appendBotMessage(data.data.message, data.data.source);
                } else {
                    this.appendBotMessage("Maaf, terjadi kesalahan pada sistem.", 'error');
                }

            } catch (error) {
                console.error(error);
                this.state.isTyping = false;
                this.dom.typingIndicator.classList.add('hidden');
                this.setPose("warning");
                this.appendBotMessage("Gagal terhubung ke server. Periksa koneksi internet Anda.", 'error');
            }
        },

        escapeHtml(unsafe) {
            return unsafe
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
        }
    };

    // Auto-init
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => window.Mascot.init());
    } else {
        window.Mascot.init();
    }

    // Support for Livewire/Filament SPA Navigation
    document.addEventListener("livewire:navigated", () => {
        if (window.Mascot) {
            window.Mascot.init();
        }
    });
}
