<style>
    /* ==========================================================================
       Comprehensive Anti-Flicker & Loading Cursor Protection:
       Locks opacity, suppresses premature spinner icons, and keeps cursor pointer.
       ========================================================================== */

    /* 1. Opacity and colors are ALWAYS locked for buttons in modals, mascot & actions */
    .fi-modal-window button:not(.fi-processing),
    .fi-modal-window button:not(.fi-processing):disabled,
    .fi-modal-window button:not(.fi-processing)[disabled],
    .fi-modal-window .fi-btn:not(.fi-processing),
    .fi-modal-window .fi-btn:not(.fi-processing):disabled,
    .fi-modal-window .fi-btn:not(.fi-processing)[disabled],
    .fi-modal-window .fi-icon-btn,
    .fi-modal-window .fi-icon-btn:disabled,
    .fi-modal-window .fi-icon-btn[disabled],
    .fi-modal-window a,
    .fi-modal-window a:disabled,
    .fi-modal-header button,
    .fi-modal-header button:disabled,
    .fi-modal-header button[disabled],
    .fi-modal-close-btn,
    .fi-modal-close-btn:disabled,
    .fi-modal-close-btn[disabled],
    .fi-modal-close-btn-ctn button,
    .fi-modal-close-btn-ctn button:disabled,
    .fi-modal-footer button:not(.fi-processing),
    .fi-modal-footer button:not(.fi-processing):disabled,
    .fi-modal-footer button:not(.fi-processing)[disabled],
    .fi-modal-footer .fi-btn:not(.fi-processing),
    .fi-modal-footer .fi-btn:not(.fi-processing):disabled,
    .fi-modal-footer .fi-btn:not(.fi-processing)[disabled],
    .fi-modal-content button:not(.fi-processing),
    .fi-modal-content button:not(.fi-processing):disabled,
    .fi-modal-content button:not(.fi-processing)[disabled],
    .fi-modal button:not(.fi-processing),
    .fi-modal button:not(.fi-processing):disabled,
    [role="dialog"] button:not(.fi-processing),
    [role="dialog"] button:not(.fi-processing):disabled,
    .fi-ta-actions button:not(.fi-processing),
    .fi-ta-actions button:not(.fi-processing):disabled,
    .fi-ta-header-toolbar button:not(.fi-processing),
    .fi-ta-header-toolbar button:not(.fi-processing):disabled,
    #kanda-putra-mascot-root button,
    #kanda-putra-mascot-root button:disabled,
    #chatbot-submit,
    #chatbot-submit:disabled,
    .fi-user-menu button,
    .fi-user-menu button:disabled {
        opacity: 1 !important;
        --tw-bg-opacity: 1 !important;
        --tw-text-opacity: 1 !important;
        --tw-border-opacity: 1 !important;
        pointer-events: auto !important;
        transition: none !important;
        animation: none !important;
        filter: none !important;
    }

    /* 2. Cursor MUST BE pointer, never 'wait', 'progress', or 'default' during polling hover */
    button:not(.fi-processing),
    button:not(.fi-processing):hover,
    button:not(.fi-processing):disabled,
    .fi-btn:not(.fi-processing),
    .fi-btn:not(.fi-processing):hover,
    .fi-btn:not(.fi-processing):disabled,
    .fi-icon-btn:not(.fi-processing),
    .fi-icon-btn:not(.fi-processing):hover,
    .fi-icon-btn:not(.fi-processing):disabled,
    .fi-modal-window button:not(.fi-processing),
    .fi-modal-window button:not(.fi-processing):hover,
    .fi-modal-footer button:not(.fi-processing),
    .fi-modal-footer button:not(.fi-processing):hover,
    .fi-modal-header button,
    .fi-modal-header button:hover,
    .fi-modal-close-btn,
    .fi-modal-close-btn:hover,
    .fi-modal-close-btn-ctn button,
    .fi-modal button:not(.fi-processing),
    [role="dialog"] button:not(.fi-processing),
    .fi-ta-actions button:not(.fi-processing),
    .fi-ta-actions button:not(.fi-processing):hover,
    .fi-ta-header-toolbar button:not(.fi-processing),
    .fi-ta-header-toolbar button:not(.fi-processing):hover,
    #kanda-putra-mascot-root button,
    #chatbot-submit,
    .fi-user-menu button {
        cursor: pointer !important;
    }

    /* 3. NEVER SHOW blue spinning loading indicator unless the button has .fi-processing */
    button:not(.fi-processing) .fi-loading-indicator,
    .fi-btn:not(.fi-processing) .fi-loading-indicator,
    .fi-icon-btn:not(.fi-processing) .fi-loading-indicator,
    .fi-modal-window button:not(.fi-processing) .fi-loading-indicator,
    .fi-modal-footer button:not(.fi-processing) .fi-loading-indicator,
    .fi-modal-header button .fi-loading-indicator,
    .fi-modal-close-btn .fi-loading-indicator,
    .fi-modal button:not(.fi-processing) .fi-loading-indicator,
    [role="dialog"] button:not(.fi-processing) .fi-loading-indicator,
    .fi-ta-actions button:not(.fi-processing) .fi-loading-indicator,
    .fi-ta-header-toolbar button:not(.fi-processing) .fi-loading-indicator,
    #kanda-putra-mascot-root .fi-loading-indicator {
        display: none !important;
        visibility: hidden !important;
    }

    /* 4. NEVER HIDE normal button icons during passive background polling */
    button:not(.fi-processing) > svg:not(.fi-loading-indicator),
    button:not(.fi-processing) > .fi-icon:not(.fi-loading-indicator),
    .fi-btn:not(.fi-processing) > svg:not(.fi-loading-indicator),
    .fi-btn:not(.fi-processing) > .fi-icon:not(.fi-loading-indicator),
    .fi-icon-btn:not(.fi-processing) > svg:not(.fi-loading-indicator),
    .fi-icon-btn:not(.fi-processing) > .fi-icon:not(.fi-loading-indicator),
    .fi-modal-window button:not(.fi-processing) > svg:not(.fi-loading-indicator),
    .fi-modal-footer button:not(.fi-processing) > svg:not(.fi-loading-indicator),
    .fi-modal-header button > svg,
    .fi-modal-close-btn > svg,
    .fi-modal button:not(.fi-processing) > svg:not(.fi-loading-indicator),
    [role="dialog"] button:not(.fi-processing) > svg:not(.fi-loading-indicator),
    .fi-ta-actions button:not(.fi-processing) > svg:not(.fi-loading-indicator),
    .fi-ta-header-toolbar button:not(.fi-processing) > svg:not(.fi-loading-indicator),
    #kanda-putra-mascot-root button > svg {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    /* 5. Only when actually processing a submitted form, show the loading indicator cleanly */
    button.fi-processing .fi-loading-indicator,
    .fi-btn.fi-processing .fi-loading-indicator,
    .fi-modal-window button.fi-processing .fi-loading-indicator,
    .fi-modal-footer .fi-btn.fi-processing .fi-loading-indicator {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 0.8 !important;
        cursor: wait !important;
    }
</style>
<script>
    (function () {
        const stripWireLoadingFromNonLivewireActions = () => {
            // Strip wire:loading attributes from non-processing buttons
            document.querySelectorAll('button:not(.fi-processing), .fi-btn:not(.fi-processing), .fi-icon-btn:not(.fi-processing)').forEach(button => {
                if (button.hasAttribute('wire:loading.attr') && !button.hasAttribute('wire:target')) {
                    button.removeAttribute('wire:loading.attr');
                    if (button.disabled && !button.classList.contains('fi-processing')) {
                        button.disabled = false;
                    }
                }

                // Strip wire:loading on loading indicator child SVGs if no wire:target
                button.querySelectorAll('.fi-loading-indicator, [wire\\:loading], [wire\\:loading\\.delay]').forEach(indicator => {
                    if (!indicator.hasAttribute('wire:target')) {
                        indicator.removeAttribute('wire:loading');
                        indicator.removeAttribute('wire:loading.delay');
                        indicator.style.display = 'none';
                    }
                });

                // Strip wire:loading.remove from regular icon child SVGs
                button.querySelectorAll('[wire\\:loading\\.remove], [wire\\:loading\\.remove\\.delay]').forEach(icon => {
                    if (!icon.hasAttribute('wire:target')) {
                        icon.removeAttribute('wire:loading.remove');
                        icon.removeAttribute('wire:loading.remove.delay');
                        icon.style.display = '';
                    }
                });
            });

            // Specifically clean up all buttons inside modals and dialogs
            document.querySelectorAll('.fi-modal-window button, .fi-modal button, [role="dialog"] button, #kanda-putra-mascot-root button').forEach(el => {
                el.removeAttribute('wire:loading.attr');
                if (el.disabled && !el.classList.contains('fi-processing')) {
                    el.disabled = false;
                }
            });
        };

        const initAntiFlicker = () => {
            stripWireLoadingFromNonLivewireActions();
            const observer = new MutationObserver(stripWireLoadingFromNonLivewireActions);
            observer.observe(document.body, { childList: true, subtree: true });

            const hookLivewire = () => {
                if (window.Livewire) {
                    Livewire.hook('commit', () => {
                        setTimeout(stripWireLoadingFromNonLivewireActions, 0);
                    });
                    Livewire.hook('morph.updated', () => {
                        stripWireLoadingFromNonLivewireActions();
                    });
                }
            };

            if (window.Livewire) {
                hookLivewire();
            } else {
                document.addEventListener('livewire:initialized', hookLivewire);
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAntiFlicker);
        } else {
            initAntiFlicker();
        }
    })();
</script>
