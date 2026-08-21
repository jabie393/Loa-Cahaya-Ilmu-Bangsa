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
            request()->has('download')
            ? '<script>setTimeout(function(){ if(window.downloadPDF){ try{ window.downloadPDF(); }catch(e){ window.print(); } } else { window.print(); } }, 1000);</script>'
            : (request()->has('print')
                ? '<script>setTimeout(() => { window.print(); }, 1000);</script>'
                : '')
        ))
    ]);
})->name('public.loa.preview');

Route::get('/ac/preview/{record}', function (App\Models\Submission $record) {
    if ($record->status !== 'Approved') {
        abort(403, 'Sertifikat tidak tersedia.');
    }

    $content = view($record->getAcTemplateView(), ['record' => $record])->render();

    return view('layouts.public-loa', [
        'slot' => new \Illuminate\Support\HtmlString($content . (
            request()->has('download')
            ? '<script>setTimeout(function(){ if(window.downloadPDF){ try{ window.downloadPDF(); }catch(e){ window.print(); } } else { window.print(); } }, 1000);</script>'
            : (request()->has('print')
                ? '<script>setTimeout(() => { window.print(); }, 1000);</script>'
                : '')
        ))
    ]);
})->name('public.ac.preview');

Route::get('/pfc/preview/{record}', function (App\Models\Submission $record) {
    if ($record->status !== 'Approved') {
        abort(403, 'Sertifikat tidak tersedia.');
    }

    $content = view($record->getPfcTemplateView(), ['record' => $record])->render();

    return view('layouts.public-loa', [
        'slot' => new \Illuminate\Support\HtmlString($content . (
            request()->has('download')
            ? '<script>setTimeout(function(){ if(window.downloadPDF){ try{ window.downloadPDF(); }catch(e){ window.print(); } } else { window.print(); } }, 1000);</script>'
            : (request()->has('print')
                ? '<script>setTimeout(() => { window.print(); }, 1000);</script>'
                : '')
        ))
    ]);
})->name('public.pfc.preview');



Route::get('/chatbot/faqs', [\App\Http\Controllers\ChatbotController::class, 'getFaqs'])->name('chatbot.faqs');
Route::get('/chatbot/session', [\App\Http\Controllers\ChatbotController::class, 'getSession'])->name('chatbot.session');
Route::post('/chatbot/message', [\App\Http\Controllers\ChatbotController::class, 'sendMessage'])->name('chatbot.message')->middleware('throttle:30,1');



// SSO Server login route
Route::get('/sso/login', function (\Illuminate\Http\Request $request) {
    $redirect = $request->query('redirect');

    if (empty($redirect)) {
        return abort(400, 'Redirect parameter is required.');
    }

    if (!\Illuminate\Support\Facades\Auth::check()) {
        session(['sso_redirect' => $redirect]);
        return redirect('/login?sso=1');
    }

    $user = \Illuminate\Support\Facades\Auth::user();
    $expiresAt = now()->addMinutes(5)->timestamp;
    $ssoSecret = env('SSO_SECRET', 'cib_sso_secret_key_2026_jwt');

    $signature = hash_hmac('sha256', $user->id . '|' . $expiresAt, $ssoSecret);

    // Pull sso_redirect from session just in case
    session()->forget('sso_redirect');

    // Parse the redirect URL and append parameters
    $separator = strpos($redirect, '?') === false ? '?' : '&';
    $callbackUrl = $redirect . $separator . http_build_query([
        'user_id' => $user->id,
        'expires_at' => $expiresAt,
        'signature' => $signature,
    ]);

    return redirect($callbackUrl);
})->name('sso.login');


// SSO Silent check route (Auto-detect status login)
Route::get('/sso/silent-check', function (\Illuminate\Http\Request $request) {
    $redirect = $request->query('redirect');
    $fallback = $request->query('fallback', '/');

    if (empty($redirect)) {
        return abort(400, 'Redirect parameter is required.');
    }

    if (\Illuminate\Support\Facades\Auth::check()) {
        $user = \Illuminate\Support\Facades\Auth::user();
        $expiresAt = now()->addMinutes(5)->timestamp;
        $ssoSecret = env('SSO_SECRET', 'cib_sso_secret_key_2026_jwt');
        $signature = hash_hmac('sha256', $user->id . '|' . $expiresAt, $ssoSecret);

        $separator = strpos($redirect, '?') === false ? '?' : '&';
        $callbackUrl = $redirect . $separator . http_build_query([
            'user_id' => $user->id,
            'expires_at' => $expiresAt,
            'signature' => $signature,
            'fallback' => $fallback,
        ]);
        return redirect($callbackUrl);
    }

    return redirect($fallback);
})->name('sso.silent-check');


// SSO Iframe check route (Auto-detect status login in background)
Route::get('/sso/iframe-check', function (\Illuminate\Http\Request $request) {
    $origin = $request->query('origin');
    if (empty($origin)) {
        return response('Origin required', 400);
    }

    $origin = urldecode($origin);

    $user = \Illuminate\Support\Facades\Auth::user();
    
    \Illuminate\Support\Facades\Log::info('SSO Iframe check debug', [
        'has_loa_session_cookie' => $request->hasCookie('loa_session'),
        'cookies_received' => array_keys($request->cookies->all()),
        'user' => $user ? $user->id : null,
        'session_id' => $request->session()->getId(),
        'origin' => $request->query('origin'),
    ]);

    $data = ['logged_in' => false];

    if ($user) {
        $expiresAt = now()->addMinutes(5)->timestamp;
        $ssoSecret = env('SSO_SECRET', 'cib_sso_secret_key_2026_jwt');
        $signature = hash_hmac('sha256', $user->id . '|' . $expiresAt, $ssoSecret);

        $data = [
            'logged_in' => true,
            'user_id' => $user->id,
            'expires_at' => $expiresAt,
            'signature' => $signature,
        ];
    }

    $jsonData = json_encode($data);

    $targetUrl = env('REPO_URL', 'http://127.0.0.1:8001');
    $targetHost = parse_url($targetUrl, PHP_URL_HOST);
    $targetPort = parse_url($targetUrl, PHP_URL_PORT);
    $portSuffix = $targetPort ? ':' . $targetPort : '';
    $allowedOrigins = "http://" . $targetHost . $portSuffix . " https://" . $targetHost . $portSuffix;

    return response($jsonData ? "
        <!DOCTYPE html>
        <html>
        <body>
        <script>
            window.parent.postMessage({
                type: 'cib_sso_status',
                data: {$jsonData}
            }, '*');
        </script>
        </body>
        </html>
    " : "")
    ->header('Content-Type', 'text/html')
    ->header('Content-Security-Policy', "frame-ancestors 'self' http://127.0.0.1:8001 http://localhost:8001 " . $allowedOrigins)
    ->header('X-Frame-Options', 'ALLOWALL');
})->name('sso.iframe-check');

 
// SSO AJAX Synchronization Routes (from Repository)
Route::post('/sso/callback-ajax', function (\Illuminate\Http\Request $request) {
    $userId = $request->input('user_id');
    $expiresAt = $request->input('expires_at');
    $signature = $request->input('signature');

    if (empty($userId) || empty($expiresAt) || empty($signature)) {
        return response()->json(['success' => false, 'message' => 'Missing parameters'], 400);
    }

    if (now()->timestamp > $expiresAt) {
        return response()->json(['success' => false, 'message' => 'Expired token'], 403);
    }

    $ssoSecret = env('SSO_SECRET', 'cib_sso_secret_key_2026_jwt');
    $expectedSignature = hash_hmac('sha256', $userId . '|' . $expiresAt, $ssoSecret);

    if (!hash_equals($expectedSignature, $signature)) {
        return response()->json(['success' => false, 'message' => 'Invalid signature'], 403);
    }

    $user = \App\Models\User::find($userId);
    if (!$user) {
        return response()->json(['success' => false, 'message' => 'User not found'], 404);
    }

    \Illuminate\Support\Facades\Auth::login($user, true);

    return response()->json(['success' => true]);
});

Route::post('/sso/logout-ajax', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return response()->json(['success' => true]);
});


// SSO Single Log-Out (SLO) Route
Route::get('/sso/logout', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    $redirect = $request->query('redirect');
    $sso = $request->query('sso');

    if ($sso) {
        return redirect($redirect ?: '/');
    }

    $repoUrl = env('REPO_URL', 'http://127.0.0.1:8001');
    return redirect($repoUrl . '/sso/logout?sso=true&redirect=' . urlencode($redirect ?: 'http://127.0.0.1:8000'));
})->name('sso.logout');


// SSO Local Session Check Route
Route::post('/sso/local-check', function () {
    return response()->json([
        'logged_in' => \Illuminate\Support\Facades\Auth::check()
    ]);
})->name('sso.local-check');
