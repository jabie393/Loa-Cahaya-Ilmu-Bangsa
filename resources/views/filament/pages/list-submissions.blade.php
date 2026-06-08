<x-filament-panels::page>
    <!-- Instructions & Action Banner -->
    <div
        class="border-primary-100 bg-primary-50 dark:border-primary-900/30 dark:bg-primary-900/20 mb-4 rounded-2xl border p-6">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div class="space-y-2">
                <h3 class="text-primary-900 dark:text-primary-100 text-xl font-bold tracking-tight">
                    Panduan Pengajuan Letter of Acceptance (LOA)
                </h3>
                <ul
                    class="text-primary-700 dark:text-primary-300 list-outside list-disc space-y-1.5 ml-6 text-sm font-medium">
                    <li>Klik tombol <strong>Buat Pengajuan Baru</strong> di pojok kanan atas untuk memulai pengajuan.
                    </li>
                    <li>Isi data naskah Anda: penulis, email, instansi, jurnal target, judul, abstrak, kata kunci, serta
                        <strong>daftar pustaka / referensi</strong>.</li>
                    <li>Unggah <strong>File PDF naskah</strong> (sesuai template jurnal) dan <strong>Bukti
                            Pembayaran</strong> pada panel samping kanan.</li>
                    <li>Klik <strong>Next</strong> untuk meninjau kembali ringkasan data Anda pada halaman konfirmasi.
                    </li>
                    <li>Centang persetujuan syarat & ketentuan, kemudian klik <strong>Create</strong> untuk mengirimkan
                        pengajuan.</li>
                    <li>Klik tombol <strong>Konfirmasi LOA ke Admin</strong> dan konfirmasi ID untuk approval LOA.</li>
                    <li>Setelah disetujui oleh admin (Status: Approved), naskah Anda akan otomatis terbit di OJS dan
                        tombol download untuk <strong>LOA</strong>, <strong>Certificate (AC)</strong>, dan
                        <strong>PFC</strong> akan muncul pada menu aksi tabel di bawah.</li>
                </ul>
            </div>

        </div>
    </div>

    <!-- Render the default table -->
    {{ $this->table }}
</x-filament-panels::page>