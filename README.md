<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## 🔔 Konfigurasi Push Notifikasi & Real-time Widget (F Loafinwatch)

Backend ini terintegrasi dengan Firebase Cloud Messaging (**FCM HTTP v1**) untuk mengirim push notifikasi dan memperbarui Android Homescreen Widget di aplikasi pendamping **F Loafinwatch** secara otomatis saat pembayaran payout QRIS developer selesai diproses.

### 1. Download Service Account Key
1. Buka [Firebase Console](https://console.firebase.google.com/) &rarr; pilih project **floafinwatch**.
2. Masuk ke **Project Settings (ikon Gear)** &rarr; tab **Service accounts**.
3. Klik tombol **Generate new private key** &rarr; simpan file JSON yang terunduh.
4. Rename dan tempatkan file tersebut di:
   ```
   storage/app/firebase/service-account.json
   ```
   *(File ini sudah otomatis diabaikan oleh `.gitignore` sehingga aman dan tidak akan terunggah ke repositori Git)*.

### 2. Migrasi Database
Pastikan kolom `fcm_token` sudah terdaftar pada tabel `users`:
```bash
php artisan migrate
```

### 3. Menguji Pengiriman Notifikasi & Sinkronisasi Widget
Ada 2 cara untuk menguji integrasi:

#### A. Menggunakan Skrip Simulasi Payout Lengkap (Reverb + FCM + Auto Cleanup)
Skrip ini akan membuat data dummy payout, mengirim event WebSocket Reverb, memicu push notifikasi FCM, dan dapat dibersihkan (*clean up*) kembali:
```bash
# 1. Buat dummy payout & kirim notifikasi FCM + Reverb
php scripts/test_realtime_payout.php

# Kustomisasi nominal dan ID (opsional)
php scripts/test_realtime_payout.php --amount=100000 --id=99999

# 2. Bersihkan (Cleanup) data dummy setelah selesai testing
php scripts/test_realtime_payout.php --cleanup
```

#### B. Menggunakan Artisan Command (FCM Langsung)
```bash
# Kirim notifikasi uji coba
php artisan dev:test-fcm

# Kustomisasi judul dan pesan
php artisan dev:test-fcm --title="Judul Notif" --body="Isi pesan notifikasi"
```

### 4. Trigger Otomatis di Sistem
Notifikasi dan sinkronisasi widget dikirim secara otomatis oleh sistem saat:
* Admin mengonfirmasi pembayaran payout developer via QRIS pada halaman Filament **Dev Payouts** (`App\Livewire\DevPayoutsTable`).
* Service `FirebaseNotificationService::notifyDeveloper()` akan mengirim sinyal `action: 'sync_widgets'` ke perangkat developer untuk mengupdate status saldo dan grafik tren widget di homescreen HP secara realtime di background.

---

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
