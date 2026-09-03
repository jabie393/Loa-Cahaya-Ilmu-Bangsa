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

    if (is_array($record->authors) && count($record->authors) > 5) {
        $appendix = view('filament.resources.submissions.pages.loa-appendix', ['record' => $record])->render();
        $content .= "\n" . $appendix;
    }
    
    $overrideScript = '
    <script>
        (function() {
            const originalArea = document.getElementById("capture-area");
            const appendix = document.getElementById("loa-appendix-container");
            
            if (originalArea && appendix) {
                // 1. Rename original capture-area to capture-page-1 and force 297mm height
                originalArea.id = "capture-page-1";
                originalArea.style.height = "297mm";
                originalArea.style.maxHeight = "297mm";
                originalArea.style.overflow = "hidden";
                
                // 2. Create wrapper capture-area
                const wrapper = document.createElement("div");
                wrapper.id = "capture-area";
                wrapper.style.width = "210mm";
                wrapper.style.margin = "0 auto";
                wrapper.style.background = "white";
                
                // 3. Insert wrapper before capture-page-1
                originalArea.parentNode.insertBefore(wrapper, originalArea);
                
                // 4. Move elements inside wrapper
                wrapper.appendChild(originalArea);
                
                // Move all appendix pages inside wrapper and show them
                const pages = appendix.querySelectorAll(".loa-appendix-page");
                pages.forEach(page => {
                    wrapper.appendChild(page);
                });
                
                // Remove the empty appendix container
                appendix.remove();
            }

            // Override downloadPDF to support multi-page printing
            const originalDownloadPDF = window.downloadPDF;
            if (originalDownloadPDF) {
                window.downloadPDF = async function() {
                    const { jsPDF } = window.jspdf;
                    const element = document.querySelector("#capture-area");
                    const btn = document.querySelector("#download-btn");

                    if (btn) {
                        btn.style.opacity = "0.5";
                        btn.innerText = "Processing...";
                    }

                    try {
                        const body = document.querySelector("body");
                        const originalBodyMaxHeight = body ? body.style.maxHeight : "";
                        const originalBodyBoxShadow = body ? body.style.boxShadow : "";
                        const originalBodyMargin = body ? body.style.margin : "";
                        
                        if (body) {
                            body.style.maxHeight = "none";
                            body.style.boxShadow = "none";
                            body.style.margin = "0";
                            body.classList.remove("max-h-[297mm]");
                        }

                        const canvas = await html2canvas(element, {
                            scale: 3, // High resolution scale for crisp text
                            useCORS: true,
                            allowTaint: true,
                            backgroundColor: "#ffffff",
                            onclone: (clonedDoc) => {
                                const downloadBtn = clonedDoc.querySelector("#download-btn");
                                if (downloadBtn) downloadBtn.style.display = "none";
                            }
                        });

                        // Revert body styles
                        if (body) {
                            body.style.maxHeight = originalBodyMaxHeight;
                            body.style.boxShadow = originalBodyBoxShadow;
                            body.style.margin = originalBodyMargin;
                        }

                        const imgData = canvas.toDataURL("image/jpeg", 0.95); // High quality JPEG 95%
                        const imgWidth = 210;
                        const pageHeight = 297;
                        const imgHeight = (canvas.height * imgWidth) / canvas.width;
                        let heightLeft = imgHeight;
                        let position = 0;

                        const pdf = new jsPDF({
                            orientation: "portrait",
                            unit: "mm",
                            format: "a4"
                        });

                        pdf.addImage(imgData, "JPEG", 0, position, imgWidth, imgHeight, undefined, "FAST");
                        heightLeft -= pageHeight;

                        while (heightLeft > 2) {
                            position = heightLeft - imgHeight;
                            pdf.addPage();
                            pdf.addImage(imgData, "JPEG", 0, position, imgWidth, imgHeight, undefined, "FAST");
                            heightLeft -= pageHeight;
                        }
                        pdf.save("LOA-" + ' . json_encode($record->author_name) . ' + ".pdf");
                    } catch (e) {
                        console.error(e);
                        window.print();
                    } finally {
                        if (btn) {
                            btn.style.opacity = "1";
                            btn.innerText = "Download PDF";
                        }
                    }
                };
            }
        })();
    </script>';
    
    $content .= $overrideScript;

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

    $view = $record->getAcTemplateView();
    $content = view($view, ['record' => $record])->render();

    if (is_array($record->authors) && count($record->authors) > 5) {
        $appendix = view('filament.resources.submissions.pages.ac-appendix', ['record' => $record])->render();
        $content .= "\n" . $appendix;
    }
    
    $overrideScript = '
    <script>
        (function() {
            const originalArea = document.querySelector("[data-purpose=\"main-certificate-frame\"]");
            const appendix = document.getElementById("ac-appendix-container");
            
            if (originalArea && appendix) {
                // 1. Rename original and lock page 1 height/width
                originalArea.removeAttribute("data-purpose");
                originalArea.setAttribute("data-purpose", "main-certificate-frame-page-1");
                originalArea.style.width = "297mm";
                originalArea.style.height = "210mm";
                originalArea.style.maxHeight = "210mm";
                originalArea.style.overflow = "hidden";
                
                // 2. Create wrapper
                const wrapper = document.createElement("div");
                wrapper.setAttribute("data-purpose", "main-certificate-frame");
                wrapper.style.width = "297mm";
                wrapper.style.margin = "0 auto";
                wrapper.style.background = "white";
                
                // 3. Insert wrapper before page 1
                originalArea.parentNode.insertBefore(wrapper, originalArea);
                
                // 4. Move elements inside wrapper
                wrapper.appendChild(originalArea);
                
                const pages = appendix.querySelectorAll(".ac-appendix-page");
                pages.forEach(page => {
                    wrapper.appendChild(page);
                });
                
                appendix.remove();
            }

            // Override downloadPDF to support multi-page printing
            const originalDownloadPDF = window.downloadPDF;
            if (originalDownloadPDF) {
                window.downloadPDF = async function() {
                    const { jsPDF } = window.jspdf;
                    const element = document.querySelector("[data-purpose=\"main-certificate-frame\"]");
                    const btn = document.querySelector("#download-btn");

                    if (btn) {
                        btn.style.opacity = "0.5";
                        btn.innerText = "Processing...";
                    }

                    try {
                        const body = document.querySelector("body");
                        const originalBodyMaxHeight = body ? body.style.maxHeight : "";
                        const originalBodyBoxShadow = body ? body.style.boxShadow : "";
                        const originalBodyMargin = body ? body.style.margin : "";
                        
                        if (body) {
                            body.style.maxHeight = "none";
                            body.style.boxShadow = "none";
                            body.style.margin = "0";
                            body.classList.remove("max-h-[297mm]");
                        }

                        const canvas = await html2canvas(element, {
                            scale: 3, // Keep scale 3 for ultra sharpness
                            useCORS: true,
                            allowTaint: true,
                            backgroundColor: "#ffffff",
                            onclone: (clonedDoc) => {
                                const downloadBtn = clonedDoc.querySelector("#download-btn");
                                if (downloadBtn) downloadBtn.style.display = "none";
                            }
                        });

                        // Revert body styles
                        if (body) {
                            body.style.maxHeight = originalBodyMaxHeight;
                            body.style.boxShadow = originalBodyBoxShadow;
                            body.style.margin = originalBodyMargin;
                        }

                        const imgData = canvas.toDataURL("image/jpeg", 0.95); // High quality JPEG
                        const imgWidth = 297; // Landscape A4 width
                        const pageHeight = 210; // Landscape A4 height
                        const imgHeight = (canvas.height * imgWidth) / canvas.width;
                        let heightLeft = imgHeight;
                        let position = 0;

                        const pdf = new jsPDF({
                            orientation: "landscape",
                            unit: "mm",
                            format: "a4"
                        });

                        pdf.addImage(imgData, "JPEG", 0, position, imgWidth, imgHeight, undefined, "FAST");
                        heightLeft -= pageHeight;

                        while (heightLeft > 2) {
                            position = heightLeft - imgHeight;
                            pdf.addPage("a4", "landscape");
                            pdf.addImage(imgData, "JPEG", 0, position, imgWidth, imgHeight, undefined, "FAST");
                            heightLeft -= pageHeight;
                        }
                        pdf.save("Certificate-" + ' . json_encode($record->author_name) . ' + ".pdf");
                    } catch (e) {
                        console.error(e);
                        window.print();
                    } finally {
                        if (btn) {
                            btn.style.opacity = "1";
                            btn.innerText = "Download Certificate";
                        }
                    }
                };
            }
        })();
    </script>';
    
    $content .= $overrideScript;

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

    $view = $record->getPfcTemplateView();
    $content = view($view, ['record' => $record])->render();

    if (is_array($record->authors) && count($record->authors) > 5) {
        $appendix = view('filament.resources.submissions.pages.pfc-appendix', ['record' => $record])->render();
        $content .= "\n" . $appendix;
    }
    
    $overrideScript = '
    <script>
        (function() {
            const originalArea = document.querySelector("[data-purpose=\"certificate-main-layout\"]");
            const appendix = document.getElementById("pfc-appendix-container");
            
            if (originalArea && appendix) {
                // 1. Rename original and lock page 1 height/width
                originalArea.removeAttribute("data-purpose");
                originalArea.setAttribute("data-purpose", "certificate-main-layout-page-1");
                originalArea.style.width = "210mm";
                originalArea.style.height = "297mm";
                originalArea.style.maxHeight = "297mm";
                originalArea.style.overflow = "hidden";
                
                // 2. Create wrapper
                const wrapper = document.createElement("div");
                wrapper.setAttribute("data-purpose", "certificate-main-layout");
                wrapper.style.width = "210mm";
                wrapper.style.margin = "0 auto";
                wrapper.style.background = "white";
                
                // 3. Insert wrapper before page 1
                originalArea.parentNode.insertBefore(wrapper, originalArea);
                
                // 4. Move elements inside wrapper
                wrapper.appendChild(originalArea);
                
                const pages = appendix.querySelectorAll(".pfc-appendix-page");
                pages.forEach(page => {
                    wrapper.appendChild(page);
                });
                
                appendix.remove();
            }

            // Override downloadPDF to support multi-page printing
            const originalDownloadPDF = window.downloadPDF;
            if (originalDownloadPDF) {
                window.downloadPDF = async function() {
                    const { jsPDF } = window.jspdf;
                    const element = document.querySelector("[data-purpose=\"certificate-main-layout\"]");
                    const btn = document.querySelector("#download-btn");

                    if (btn) {
                        btn.style.opacity = "0.5";
                        btn.innerText = "Processing...";
                    }

                    try {
                        const body = document.querySelector("body");
                        const originalBodyMaxHeight = body ? body.style.maxHeight : "";
                        const originalBodyBoxShadow = body ? body.style.boxShadow : "";
                        const originalBodyMargin = body ? body.style.margin : "";
                        
                        if (body) {
                            body.style.maxHeight = "none";
                            body.style.boxShadow = "none";
                            body.style.margin = "0";
                            body.classList.remove("max-h-[297mm]");
                        }

                        const canvas = await html2canvas(element, {
                            scale: 3, // Keep scale 3 for ultra sharpness
                            useCORS: true,
                            allowTaint: true,
                            backgroundColor: "#ffffff",
                            onclone: (clonedDoc) => {
                                const downloadBtn = clonedDoc.querySelector("#download-btn");
                                if (downloadBtn) downloadBtn.style.display = "none";
                            }
                        });

                        // Revert body styles
                        if (body) {
                            body.style.maxHeight = originalBodyMaxHeight;
                            body.style.boxShadow = originalBodyBoxShadow;
                            body.style.margin = originalBodyMargin;
                        }

                        const imgData = canvas.toDataURL("image/jpeg", 0.95); // High quality JPEG
                        const imgWidth = 210; // Portrait A4 width
                        const pageHeight = 297; // Portrait A4 height
                        const imgHeight = (canvas.height * imgWidth) / canvas.width;
                        let heightLeft = imgHeight;
                        let position = 0;

                        const pdf = new jsPDF({
                            orientation: "portrait",
                            unit: "mm",
                            format: "a4"
                        });

                        pdf.addImage(imgData, "JPEG", 0, position, imgWidth, imgHeight, undefined, "FAST");
                        heightLeft -= pageHeight;

                        while (heightLeft > 2) {
                            position = heightLeft - imgHeight;
                            pdf.addPage();
                            pdf.addImage(imgData, "JPEG", 0, position, imgWidth, imgHeight, undefined, "FAST");
                            heightLeft -= pageHeight;
                        }
                        pdf.save("Plagiarism_Free_Certificate_" + ' . json_encode($record->author_name) . ' + ".pdf");
                    } catch (e) {
                        console.error(e);
                        window.print();
                    } finally {
                        if (btn) {
                            btn.style.opacity = "1";
                            btn.innerText = "Download PDF";
                        }
                    }
                };
            }
        })();
    </script>';
    
    $content .= $overrideScript;

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
    $register = $request->query('register');

    if (empty($redirect)) {
        return abort(400, 'Redirect parameter is required.');
    }

    if (!\Illuminate\Support\Facades\Auth::check()) {
        session(['sso_redirect' => $redirect]);
        if ($register) {
            return redirect('/register?sso=1');
        }
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


// =========================================================================
// MIDTRANS PAYMENT & WEBHOOK ROUTES
// =========================================================================
Route::middleware(['auth'])->group(function () {
    Route::get('/submissions/{id}/payment', [\App\Http\Controllers\PaymentController::class, 'show'])->name('submissions.payment');
    Route::get('/submissions/{id}/payment/check', [\App\Http\Controllers\PaymentController::class, 'checkStatus'])->name('submissions.payment.check');
    Route::post('/submissions/{id}/payment/regenerate', [\App\Http\Controllers\PaymentController::class, 'regenerate'])->name('submissions.payment.regenerate');
    Route::get('/submissions/{id}/payment-doi', [\App\Http\Controllers\PaymentController::class, 'showDoi'])->name('submissions.payment.doi');
    Route::get('/submissions/{id}/payment-doi/check', [\App\Http\Controllers\PaymentController::class, 'checkDoiStatus'])->name('submissions.payment.doi.check');
    Route::post('/submissions/{id}/payment-doi/regenerate', [\App\Http\Controllers\PaymentController::class, 'regenerateDoi'])->name('submissions.payment.doi.regenerate');

});

Route::post('/midtrans/webhook', [\App\Http\Controllers\MidtransWebhookController::class, 'handle'])->name('midtrans.webhook');
Route::post('/api/midtrans/webhook', [\App\Http\Controllers\MidtransWebhookController::class, 'handle']);


Route::get('/invoice/preview/{record}', function (App\Models\Submission $record) {
    // 1. Get direct paid payments (e.g. single submission or DOI addon)
    $directPayments = $record->payments()->where('payment_status', 'paid')->get();

    // 2. Get bulk paid payments via payment_items
    $bulkPayments = \App\Models\PaymentItem::where('submission_id', $record->id)
        ->whereHas('payment', fn($q) => $q->where('payment_status', 'paid'))
        ->with('payment')
        ->get()
        ->pluck('payment')
        ->filter();

    // 3. Merge both collections
    $paidPayments = $directPayments->merge($bulkPayments)->unique('id')->values();

    if ($paidPayments->isEmpty()) {
        $payment = $record->latestPayment;
        if (!$payment) {
            abort(404, 'Data pembayaran belum ditemukan untuk naskah ini.');
        }
        $paidPayments = collect([$payment]);
    }

    // Publication payment must be specifically 'submission' or 'bulk_submission'
    $submissionPayment = $paidPayments->first(fn($p) => in_array($p->type, ['submission', 'bulk_submission']));
    $doiPayment = $paidPayments->firstWhere('type', 'doi_addon');

    if ($submissionPayment) {
        $submissionPayment->ensureInvoiceNumber();
    }
    if ($doiPayment) {
        $doiPayment->ensureInvoiceNumber();
    }

    $pricingService = app(App\Services\SubmissionPricingService::class);
    $pricing = $pricingService->calculate($record);

    $publicationAmount = 0;
    if ($submissionPayment) {
        if ($submissionPayment->type === 'bulk_submission') {
            $item = \App\Models\PaymentItem::where('payment_id', $submissionPayment->id)
                ->where('submission_id', $record->id)
                ->first();
            $publicationAmount = $item ? $item->gross_amount : $pricing['gross_amount'];
        } else {
            $publicationAmount = $submissionPayment->gross_amount;
        }
    }

    $doiAmount = $doiPayment ? $doiPayment->gross_amount : 0;
    $totalPaid = $publicationAmount + $doiAmount;
    $latestPaidAt = $paidPayments->max('paid_at') ?? $paidPayments->max('created_at');

    return view('filament.invoice.invoice', [
        'submission' => $record,
        'paidPayments' => $paidPayments,
        'submissionPayment' => $submissionPayment,
        'doiPayment' => $doiPayment,
        'publicationAmount' => $publicationAmount,
        'doiAmount' => $doiAmount,
        'totalPaid' => $totalPaid,
        'latestPaidAt' => $latestPaidAt,
        'pricing' => $pricing,
    ]);
})->name('public.invoice.preview');

Route::get('/invoice/bulk/{payment}', function (App\Models\Payment $payment) {
    if (!$payment->isPaid() && $payment->payment_status !== 'paid') {
        $qrisService = app(App\Services\MidtransQrisService::class);
        $payment = $qrisService->checkStatusFromMidtrans($payment);
    }

    $payment->ensureInvoiceNumber();
    $items = $payment->items()->with(['submission.journal', 'submission.user'])->get();

    if ($items->isEmpty()) {
        $subIds = $payment->submission_ids ?: ($payment->submission_id ? [$payment->submission_id] : []);
        $submissions = App\Models\Submission::with(['journal', 'user'])->whereIn('id', $subIds)->get();
        $pricingService = app(App\Services\SubmissionPricingService::class);
        $bulkPricing = $pricingService->calculateBulk($submissions);
        foreach ($bulkPricing['items'] as $itemData) {
            $sub = $itemData['submission'];
            $pr = $itemData['pricing'];
            App\Models\PaymentItem::create([
                'payment_id' => $payment->id,
                'submission_id' => $sub->id,
                'item_type' => 'publication',
                'item_name' => 'Naskah #' . $sub->id . ' - ' . ($sub->title ?: 'Artikel'),
                'gross_amount' => $pr['gross_amount'],
                'journal_share' => $pr['journal_share'],
                'developer_gross_share' => $pr['developer_gross_share'],
                'mdr_amount' => $pr['mdr_amount'],
                'developer_net_share' => $pr['developer_net_share'],
            ]);
        }
        $items = $payment->items()->with(['submission.journal', 'submission.user'])->get();
    }

    return view('filament.invoice.invoice-bulk', [
        'payment' => $payment,
        'items' => $items,
    ]);
})->name('public.invoice.bulk.preview');


// Bulk Payment status check
Route::get('/payments/{paymentId}/check-bulk', [App\Http\Controllers\PaymentController::class, 'checkBulkStatus'])->name('payments.check.bulk');
Route::post('/payments/{paymentId}/regenerate-bulk', [App\Http\Controllers\PaymentController::class, 'regenerateBulk'])->name('payments.regenerate.bulk');
