<x-filament-panels::page>
    <!-- Instructions & Action Banner -->
    <div class="border-primary-100 bg-primary-50 dark:border-primary-900/30 dark:bg-primary-900/20 mb-4 rounded-2xl border p-6">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div class="space-y-2">
                <h3 class="text-primary-900 dark:text-primary-100 text-xl font-bold tracking-tight">
                    Panduan Pengajuan Letter of Acceptance (LOA)
                </h3>
                <ul class="text-primary-700 dark:text-primary-300 list-inside list-disc space-y-1 text-sm font-medium">
                    <li>Klik tombol <strong>Buat Pengajuan Baru</strong> di pojok kanan atas untuk memulai proses.</li>
                    <li>Isikan data artikel dan informasi instansi Anda pada form yang tersedia.</li>
                    <li>Upload bukti pembayaran</li>
                    <li>Klik <strong>Next</strong> untuk melanjutkan ke tahap konfirmasi.</li>
                    <li>Klik <strong>Submit</strong> untuk mengirimkan pengajuan Anda.</li>
                </ul>
            </div>

        </div>
    </div>

    <!-- Render the default table -->
    {{ $this->table }}
</x-filament-panels::page>
