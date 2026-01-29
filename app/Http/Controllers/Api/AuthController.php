<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan salah.'],
            ]);
        }

        // Revoke all tokens mostly for single session strictness, or just create new one
        // $user->tokens()->delete(); 

        // Check Tenant Status & Subscription
        if ($user->tenant) {
            if ($user->tenant->hasExpired()) {
                if ($user->tenant->status !== 'suspended') {
                    $user->tenant->update(['status' => 'suspended']);
                }
                throw ValidationException::withMessages([
                    'email' => ['Masa berlangganan tenant Anda telah habis. Silakan perpanjang.'],
                ]);
            }

            if ($user->tenant->status !== 'active') {
                throw ValidationException::withMessages([
                    'email' => ['Akun tenant ini sedang dinonaktifkan/suspended.'],
                ]);
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Load relationships needed for frontend store
        $user->load(['tenant', 'branch']);

        // Determine redirect path based on role
        $redirectPath = '/';
        if ($user->role === 'super_admin') {
            $redirectPath = '/admin/dashboard';
        }

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => $user,
                'tenant' => $user->tenant,
                'redirectPath' => $redirectPath
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['tenant', 'branch']);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'tenant' => $user->tenant
            ]
        ]);
    }
}
