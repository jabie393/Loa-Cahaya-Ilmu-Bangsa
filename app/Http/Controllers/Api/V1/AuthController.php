<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Authenticate user with email and password.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', trim($validated['email']))->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau kata sandi yang Anda masukkan salah.',
            ], 401);
        }

        // Generate high-entropy API token
        $token = Str::random(64);

        // Store token in Cache for 30 days (persistent session)
        $cacheKey = "api_token:{$token}";
        Cache::put($cacheKey, $user->id, now()->addDays(30));

        // Format roles
        $roles = $user->roles->pluck('name')->toArray();

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $roles,
                'has_pin' => !empty($user->pin),
            ],
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak terautentikasi.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray(),
                'has_pin' => !empty($user->pin),
            ],
        ]);
    }

    /**
     * Verify user PIN.
     */
    public function verifyPin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pin' => 'required|digits:6',
        ]);

        $user = $request->user();

        if (empty($user->pin)) {
            return response()->json([
                'success' => false,
                'has_pin' => false,
                'message' => 'Akun Anda belum memiliki PIN.',
            ], 400);
        }

        if (!Hash::check($validated['pin'], $user->pin)) {
            return response()->json([
                'success' => false,
                'message' => 'PIN salah, coba lagi',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'PIN benar',
        ]);
    }

    /**
     * Set or update user PIN.
     */
    public function setPin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pin' => 'required|digits:6',
            'pin_confirmation' => 'required|digits:6|same:pin',
        ]);

        $user = $request->user();
        $user->pin = Hash::make($validated['pin']);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'PIN berhasil disimpan.',
        ]);
    }

    /**
     * Revoke current token and logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $header = $request->header('Authorization');

        if ($header && str_starts_with($header, 'Bearer ')) {
            $token = trim(substr($header, 7));
            if (!empty($token)) {
                Cache::forget("api_token:{$token}");
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil keluar dari akun.',
        ]);
    }
}
