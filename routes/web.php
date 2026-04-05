<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome'); 
});

// Rute bayangan 'login' agar Laravel tidak error ketika ada mekanisme fallback internal yang mencari nama route ini.
// Filament sebenarnya mengelola halamannya sendiri di /login (via AuthPanelProvider).
Route::redirect('/login-redirect', '/login')->name('login');
Route::redirect('/register-redirect', '/register')->name('register');

