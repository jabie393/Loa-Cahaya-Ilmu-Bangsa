<x-filament-panels::page>
    <!-- Instructions & Action Banner -->
    <div class="mb-4 rounded-2xl border border-primary-100 bg-primary-50 p-6 dark:border-primary-900/30 dark:bg-primary-900/20">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div class="space-y-2">
                <h3 class="text-xl font-bold tracking-tight text-primary-900 dark:text-primary-100">
                    Panduan Pengajuan Letter of Acceptance (LOA)
                </h3>
                <ul class="list-inside list-disc space-y-1 text-sm font-medium text-primary-700 dark:text-primary-300">
                    <li>Siapkan data artikel dan informasi instansi Anda.</li>
                    <li>Lakukan pembayaran melalui QRIS yang tertera pada form.</li>
                    <li>Simpan bukti pembayaran berupa screenshot/foto.</li>
                    <li>Klik tombol <strong>Buat Pengajuan Baru</strong> untuk memulai proses.</li>
                </ul>
            </div>
            
        </div>
    </div>

    <!-- Render the default table -->
    {{ $this->table }}
</x-filament-panels::page>
