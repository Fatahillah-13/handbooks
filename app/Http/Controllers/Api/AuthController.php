<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function spaLogin(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'username' => ['Login gagal: username atau password salah.'],
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        return response()->json([
            'user' => $user->only(['id', 'name', 'username', 'email', 'role_id']),
        ]);
    }

    public function spaLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }

    // Login => return token
    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('username', $data['username'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Login gagal: username atau password salah.'],
            ]);
        }

        // optional: revoke existing tokens
        // $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        AuditLog::record('login', $user, [
            'username' => $user->username,
        ]);

        return response()->json([
            'user' => $user->only(['id', 'name', 'username', 'email', 'role_id']),
            'token' => $token,
        ]);
    }

    // Logout => revoke current token
    public function logout(Request $request)
    {
        $user = $request->user();
        // revoke current token:
        if ($request->bearerToken()) {
            $user->currentAccessToken()->delete();
        } else {
            // revoke all tokens
            $user->tokens()->delete();
        }

        AuditLog::record('logout', $user, [
            'username' => $user->username,
        ]);

        return response()->json(['message' => 'Logged out']);
    }
}
