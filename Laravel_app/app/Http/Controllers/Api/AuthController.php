<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_name' => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'user_name' => $data['user_name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
        ]);

        $user->assignRole('customer');

        $newToken = $user->createToken('api-token');

        UserSession::create([
            'user_id'          => $user->id,
            'session_id'       => (string) $newToken->accessToken->id,
            'ip_address'       => $request->ip(),
            'user_agent'       => (string) $request->userAgent(),
            'login_at'         => now(),
            'last_activity_at' => now(),
            'is_active'        => true,
        ]);

        return response()->json([
            'user'  => $user,
            'token' => $newToken->plainTextToken,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        /** @var User $user */
        $user = Auth::user();
        $user->tokens()->delete();

        $newToken = $user->createToken('api-token');

        $existingActive = UserSession::query()
            ->where('user_id', $user->id)
            ->where('session_id', (string) $newToken->accessToken->id)
            ->whereNull('logout_at')
            ->first();

        if ($existingActive) {
            $existingActive->update([
                'last_activity_at' => now(),
                'is_active'        => true,
            ]);
        } else {
            UserSession::create([
                'user_id'          => $user->id,
                'session_id'       => (string) $newToken->accessToken->id,
                'ip_address'       => $request->ip(),
                'user_agent'       => (string) $request->userAgent(),
                'login_at'         => now(),
                'last_activity_at' => now(),
                'is_active'        => true,
            ]);
        }

        return response()->json([
            'user'  => $user,
            'token' => $newToken->plainTextToken,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $tokenId = (string) $request->user()->currentAccessToken()->id;

        $activeSession = UserSession::query()
            ->where('user_id', $request->user()->id)
            ->where('session_id', $tokenId)
            ->whereNull('logout_at')
            ->where('is_active', true)
            ->latest('login_at')
            ->first();

        if ($activeSession) {
            $activeSession->update([
                'logout_at'        => now(),
                'last_activity_at' => now(),
                'logout_reason'    => 'logout',
                'is_active'        => false,
            ]);
        }

        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
