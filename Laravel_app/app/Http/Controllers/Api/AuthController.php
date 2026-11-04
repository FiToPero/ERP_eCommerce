<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'in:personal,business'],
            'email'     => ['required', 'email', 'max:255', 'unique:customers'],
            'password'  => ['required', 'confirmed', 'min:8', 'string'],
        ]);

        $customer = Customer::create([
            'customer_name' => $data['customer_name'],
            'type' => $data['type'] ?? 'personal',
            'web'  => $this->originWeb($request),
            'email'     => $data['email'],
            'password'  => $data['password'],
        ]);

        return response()->json([
            'message' => 'Cliente registrado exitosamente',
            'access_token' => $newToken->plainTextToken,
            'token_type' => 'Bearer',
            'customer' => $customer
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        // El guard sanctum no tiene attempt(); se valida contra el provider de customers
        $provider = Auth::createUserProvider(config('auth.guards.api.provider'));
        $customer = $provider->retrieveByCredentials(['email' => $credentials['email']]);

        if (! $customer || ! $provider->validateCredentials($customer, $credentials)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        // Una sola sesión activa por cliente: cierro los registros previos y borro los tokens
        CustomerSession::query()
            ->where('customer_id', $customer->id)
            ->active()
            ->update([
                'is_active'     => false,
                'logout_at'     => now(),
                'logout_reason' => 'new_login',
            ]);
        $customer->tokens()->delete();

        $newToken = $customer->createToken('api-token', ['*'], now()->addMinutes(120));
        $this->startSession($customer, $newToken, $request);

        return response()->json([
            'message'    => 'Login successful',
            'customer'   => $customer,
            'token'      => $newToken->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        CustomerSession::query()
            ->where('customer_id', $request->user()->id)
            ->where('session_id', (string) $token->id)
            ->active()
            ->update([
                'is_active'        => false,
                'logout_at'        => now(),
                'last_activity_at' => now(),
                'logout_reason'    => 'logout',
            ]);

        $token->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    private function startSession(Customer $customer, NewAccessToken $token, Request $request): void
    {
        CustomerSession::create([
            'customer_id'      => $customer->id,
            'web'              => $this->originWeb($request),
            'session_id'       => (string) $token->accessToken->id,
            'ip_address'       => $request->ip(),
            'user_agent'       => (string) $request->userAgent(),
            'login_at'         => now(),
            'last_activity_at' => now(),
            'is_active'        => true,
        ]);
    }

    // Origen de la petición (ej. https://cap-erp-ecommerce-dev.com): header Origin o, si falta, el Referer
    private function originWeb(Request $request): ?string
    {
        $origin = $request->headers->get('Origin') ?: $request->headers->get('Referer');

        if (! $origin) {
            return null;
        }

        $scheme = parse_url($origin, PHP_URL_SCHEME);
        $host   = parse_url($origin, PHP_URL_HOST);
        $port   = parse_url($origin, PHP_URL_PORT);

        if (! $scheme || ! $host) {
            return null;
        }

        return Str::limit($scheme.'://'.$host.($port ? ':'.$port : ''), 255, '');
    }

    /// solo dev hay que eliminar despues
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
