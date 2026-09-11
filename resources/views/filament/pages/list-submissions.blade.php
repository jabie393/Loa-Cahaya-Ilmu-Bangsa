<x-filament-panels::page>
    <style>
        /* Specific only to table action buttons to prevent dimming on wire:poll without breaking header buttons/modals */
        .fi-ta-actions button,
        .fi-ta-actions a {
            opacity: 1 !important;
        }

        .fi-ta-row [wire\:loading] {
            opacity: 1 !important;
        }

        /* Prevent table action dropdown from being clipped or flipping into navbar when few records exist */
        .fi-ta-content-ctn {
            min-height: 480px;
        }

        .fi-dropdown-panel {
            z-index: 50 !important;
        }
    </style>


    <!-- Render the default table -->
    {{ $this->table }}
</x-filament-panels::page>