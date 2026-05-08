<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Terapkan animasi ke semua section di Filament */
    .fi-section {
        animation: fadeInUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        opacity: 0;
    }

    /* Stagger effect untuk multiple sections */
    .fi-section:nth-child(1) { animation-delay: 0.1s; }
    .fi-section:nth-child(2) { animation-delay: 0.2s; }
    .fi-section:nth-child(3) { animation-delay: 0.3s; }
    .fi-section:nth-child(4) { animation-delay: 0.4s; }
</style>
