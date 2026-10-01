<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    /**
     * Handle an incoming API request by verifying the Bearer token.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Token autentikasi tidak ditemukan. Harap sertakan Bearer token.',
            ], 401);
        }

        $token = trim(substr($header, 7));

        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token autentikasi tidak valid.',
            ], 401);
        }

        // Retrieve user ID associated with this token from Cache
        $cacheKey = "api_token:{$token}";
        $userId = Cache::get($cacheKey);

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi Anda telah kedaluwarsa atau token tidak valid. Silakan login kembali.',
            ], 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.',
            ], 401);
        }

        // Authenticate user for the current request
        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
