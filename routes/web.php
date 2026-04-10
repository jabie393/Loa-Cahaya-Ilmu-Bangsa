<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome'); 
});

// Rute bayangan 'login' agar Laravel tidak error ketika ada mekanisme fallback internal yang mencari nama route ini.
// Filament sebenarnya mengelola halamannya sendiri di /dashboard/login (via AdminPanelProvider).
Route::redirect('/login-redirect', '/login')->name('login');
Route::redirect('/register-redirect', '/register')->name('register');

Route::get('/loa/preview/{record}', function (App\Models\Submission $record) {
    if ($record->status !== 'Approved') {
        abort(403, 'LOA belum disetujui atau tidak tersedia.');
    }
    
    $view = $record->getTemplateView();
    $content = view($view, ['record' => $record])->render();
    
    return view('layouts.public-loa', [
        'slot' => new \Illuminate\Support\HtmlString($content . (
            request()->has('print') 
            ? '<script>setTimeout(() => { window.print(); }, 1000);</script>' 
            : ''
        ))
    ]);
})->name('public.loa.preview');

Route::get('/ac/preview/{record}', function (App\Models\Submission $record) {
    if ($record->status !== 'Approved') {
        abort(403, 'Sertifikat tidak tersedia.');
    }
    
    $content = view('filament.ac.ac_pdf', ['record' => $record])->render();
    
    return view('layouts.public-loa', [
        'slot' => new \Illuminate\Support\HtmlString($content . (
            request()->has('print') 
            ? '<script>setTimeout(() => { window.print(); }, 1000);</script>' 
            : ''
        ))
    ]);
})->name('public.ac.preview');

Route::get('/pfc/preview/{record}', function (App\Models\Submission $record) {
    if ($record->status !== 'Approved') {
        abort(403, 'Sertifikat tidak tersedia.');
    }
    
    $content = view('filament.pfc.pfc_pdf', ['record' => $record])->render();
    
    return view('layouts.public-loa', [
        'slot' => new \Illuminate\Support\HtmlString($content . (
            request()->has('print') 
            ? '<script>setTimeout(() => { window.print(); }, 1000);</script>' 
            : ''
        ))
    ]);
})->name('public.pfc.preview');


