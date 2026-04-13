# Cron Job Setup untuk Delete Rejected Submissions

## Deskripsi
Cron job ini menghapus semua submission yang memiliki status "Rejected" dan telah ditolak selama 7 hari atau lebih sejak `rejected_date`.

## File yang Terlibat

1. **[app/Console/Commands/DeleteRejectedSubmissions.php](app/Console/Commands/DeleteRejectedSubmissions.php)**
   - Command laravel yang menangani logika penghapusan
   - Nama command: `submissions:delete-rejected`

2. **[app/Console/Kernel.php](app/Console/Kernel.php)**
   - File scheduler yang mendaftarkan command dan mengatur jadwalnya
   - Diatur untuk berjalan setiap hari pukul 02:00 AM

## Cara Setup di VPS

### Opsi 1: Menggunakan Linux Cron (Recommended)

Tambahkan satu baris berikut ke crontab Anda:

```bash
# Edit crontab
crontab -e

# Tambahkan baris ini untuk menjalankan scheduler Laravel setiap menit
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

**Penjelasan:**
- `* * * * *` - Menjalankan setiap menit (Laravel scheduler akan menentukan kapan command terbilang seharusnya berjalan)
- `/path/to/project` - Ganti dengan path absolut ke project Anda
- `php artisan schedule:run` - Menjalankan scheduler Laravel
- `>> /dev/null 2>&1` - Mengarahkan output ke /dev/null (optional, bisa diganti dengan path log file)

### Opsi 2: Menjalankan Command Secara Langsung

Jika Anda lebih suka menjalankan command secara langsung melalui cron:

```bash
crontab -e

# Tambahkan baris ini untuk menjalankan command setiap hari pukul 02:00 AM
0 2 * * * cd /path/to/project && php artisan submissions:delete-rejected >> /var/log/laravel-cron.log 2>&1
```

## Verifikasi Setup

### Test Command Secara Manual
```bash
php artisan submissions:delete-rejected
```

### Melihat Log
```bash
# Jika menggunakan Opsi 1
tail -f /var/log/laravel.log

# Jika menggunakan Opsi 2
tail -f /var/log/laravel-cron.log
```

### Mengecek Crontab
```bash
crontab -l
```

## Behavior

- **Status Penghapusan:** Hanya submission dengan status `"Rejected"` yang akan dihapus
- **Waktu Penghapusan:** Submission akan dihapus jika `rejected_date` lebih dari 7 hari lalu
- **Logging:** Proses penghapusan akan dicatat di log Laravel (`storage/logs/laravel.log`)
- **Error Handling:** Jika terjadi error, akan dicatat dengan detail error message

## Contoh Output

**Success:**
```
Deleted 5 rejected submissions older than 7 days
```

**Dari Log File:**
```
[2026-04-14 02:00:15] local.INFO: Deleted 5 rejected submissions older than 7 days
```

## Catatan Penting

1. Pastikan direktori `storage/logs/` memiliki permission write
2. Pastikan user yang menjalankan cron memiliki akses ke project directory
3. Submission yang dihapus adalah **permanent delete**, tidak bisa di-recover
4. Untuk backup data sebelum deletion, pertimbangkan untuk menambahkan soft delete atau backup terlebih dahulu
