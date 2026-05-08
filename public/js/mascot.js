/**
 * Mascot Helper JavaScript Logic
 * Handling poses, messages, and interactivity.
 */

if (!window.Mascot) {
    window.Mascot = {
        state: {
            minimized: localStorage.getItem("mascot_minimized") === "true",
            pose: "idle",
            messageQueue: [],
            isTyping: false,
            panelOpen: false,
            assetPath: "/assets/mascot/",
        },

        init() {
            this.dom = {
                container: document.getElementById("mascot-container"),
                img: document.getElementById("mascot-img"),
                bubble: document.getElementById("mascot-bubble"),
                message: document.getElementById("mascot-message"),
                panel: document.getElementById("mascot-panel"),
                minimizeIcon: document.getElementById("minimize-icon"),
                badge: document.getElementById("mascot-badge"),
                maximizeTrigger: document.getElementById("mascot-maximize-trigger"),
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
        },

        setupListeners() {
            this.dom.img.addEventListener("mouseenter", () => {
                if (this.state.pose === "idle") this.setPose("happy");
            });

            this.dom.img.addEventListener("mouseleave", () => {
                if (this.state.pose === "happy") this.setPose("idle");
            });
        },

        setPose(pose) {
            if (this.state.pose === pose) return;

            this.dom.img.classList.add("pose-changing");

            setTimeout(() => {
                this.state.pose = pose;
                this.dom.img.src = `${this.state.assetPath}${pose}.png`;
                this.dom.img.classList.remove("pose-changing");

                // Show badge for warning/success
                if (pose === "warning" || pose === "success") {
                    this.dom.badge.classList.remove("hidden");
                    if (pose === "warning")
                        this.dom.badge.classList.replace(
                            "bg-green-500",
                            "bg-red-500",
                        );
                    if (pose === "success")
                        this.dom.badge.classList.add("bg-green-500");
                } else {
                    this.dom.badge.classList.add("hidden");
                }
            }, 150);
        },

        setMessage(text, duration = 6000) {
            if (this.state.minimized) return;

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
                // Sembunyikan maskot, tampilkan trigger di pinggir
                this.dom.container.classList.add("hidden");
                this.dom.maximizeTrigger.classList.remove("hidden");
                this.dom.maximizeTrigger.classList.add("flex");
                this.hideBubble();
                this.dom.panel.classList.add("hidden");
            } else {
                // Tampilkan maskot kembali, sembunyikan trigger
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
                this.hideBubble();
            } else {
                this.dom.panel.classList.add("hidden");
            }
        },

        handleAutoMessage() {
            const path = window.location.pathname;
            let message = "Halo! Saya Kanda Putra, asisten LOA Anda. 👋";
            let pose = "idle";

            // Update Position Dinamis (SPA Support)
            if (path.includes("submissions/create")) {
                this.dom.container.style.bottom = "80px"; // bottom-20
            } else {
                this.dom.container.style.bottom = "24px"; // bottom-6
            }

            // Landing Page (Public)
            if (path === "/" || path === "/index.php") {
                message =
                    "Halo! Saya Kanda Putra. Butuh bantuan untuk pengajuan LOA? Klik saya ya! ✨";
                pose = "happy";
            }
            // Dashboard / Journal Selection
            else if (path.includes("journal")) {
                message =
                    "Silakan pilih jurnal tujuan Anda untuk melakukan pengajuan LOA. 📚";
                pose = "happy";
            }
            // Submission Create
            else if (path.includes("submissions/create")) {
                message =
                    "Pastikan semua data sudah benar dan file PDF sesuai format ya. 📄";
                pose = "thinking";
            }
            // Submission List / Status
            else if (
                path.includes("submissions") &&
                !path.includes("create") &&
                !path.includes("edit")
            ) {
                message =
                    "Anda dapat memantau status pengajuan LOA Anda di sini secara real-time. 🔍";
                pose = "idle";
            }
            // Revision / Edit
            else if (path.includes("edit")) {
                message =
                    "Silakan periksa catatan dari admin jika ada revisi yang diminta. ✍️";
                pose = "thinking";
            }
            // Pre-Submission Review (Review Pra OJS)
            else if (path.includes("pre-submission-reviews")) {
                message =
                    "Selamat datang di Review Pra-OJS! Unggah naskah Anda dan biarkan reviewer kami menganalisis kualitasnya sebelum disubmit ke jurnal. ✨";
                pose = "happy";
            }
            // Plagiarism Check
            else if (path.includes("plagiarism")) {
                message =
                    "Layanan cek plagiarisme akan membantu validasi naskah Anda. 🛡️";
                pose = "thinking";
            }

            this.setPose(pose);
            this.setMessage(message);
        },
    };

    // Auto-init
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () =>
            window.Mascot.init(),
        );
    } else {
        window.Mascot.init();
    }

    // Support for Livewire/Filament SPA Navigation
    document.addEventListener("livewire:navigated", () => {
        if (window.Mascot) {
            window.Mascot.init();
            if (typeof window.Mascot.handleAutoMessage === "function") {
                window.Mascot.handleAutoMessage();
            }
        }
    });
}
