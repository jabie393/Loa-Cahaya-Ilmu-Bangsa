<script>
    (function () {
        let isProcessingState = false;

        function setButtonsLoading(isLoading) {
            isProcessingState = isLoading;
            const buttons = document.querySelectorAll(
                '.fi-btn, .fi-ac-action, [type="submit"], button'
            );
            buttons.forEach((btn) => {
                // Jangan nonaktifkan elemen di luar panel/halaman form jika bukan tombol form
                if (!btn.closest('.fi-form-actions, .fi-page-header-actions, .fi-sc-form, form')) {
                    return;
                }

                if (isLoading) {
                    btn.setAttribute('disabled', 'disabled');
                    btn.classList.add('opacity-50', 'cursor-wait', 'pointer-events-none', 'animate-pulse');
                } else {
                    btn.removeAttribute('disabled');
                    btn.classList.remove('opacity-50', 'cursor-wait', 'pointer-events-none', 'animate-pulse');
                }
            });
        }

        const registerLivewireHooks = () => {
            if (window.Livewire && !window.Livewire._submissionHooksRegistered) {
                window.Livewire._submissionHooksRegistered = true;
                window.Livewire.hook('request', ({ respond, fail }) => {
                    setButtonsLoading(true);

                    respond(() => {
                        setButtonsLoading(false);
                    });

                    fail(() => {
                        setButtonsLoading(false);
                    });
                });
            }
        };

        if (window.Livewire) {
            registerLivewireHooks();
        } else {
            document.addEventListener('livewire:init', registerLivewireHooks);
        }

        // Tangkap event upload FilePond di fase capture (bubbling true or false)
        document.addEventListener('form-processing-started', () => {
            setButtonsLoading(true);
        }, true);

        document.addEventListener('form-processing-finished', () => {
            setButtonsLoading(false);
        }, true);
    })();
</script>
