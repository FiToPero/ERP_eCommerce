<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerSession;
use App\Support\RequestWeb;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\NewAccessToken;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $web = $this->originWeb($request);

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'in:personal,business'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('customers', 'email')->where('web', $web)],
            'password'  => ['required', 'confirmed', 'min:8', 'string'],
        ]);

        $customer = Customer::create([
            'customer_name' => $data['customer_name'],
            'type' => $data['type'] ?? 'personal',
            'web'  => $web,
            'email'     => $data['email'],
            'password'  => $data['password'],
        ]);
        $customer->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Cliente registrado exitosamente',
            'customer' => $customer
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        // Solo se autentica el customer registrado en la web desde la que se hace la petición
        $web = $this->originWeb($request);
        $provider = Auth::createUserProvider(config('auth.guards.api.provider'));
        $customer = $web
            ? $provider->retrieveByCredentials(['email' => $credentials['email'], 'web' => $web])
            : null;

        if (! $customer || ! $provider->validateCredentials($customer, $credentials)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }
        if (! $customer->hasVerifiedEmail()) {
            return response()->json(['message' => 'Please verify your email before logging in.'], 403);
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
        return RequestWeb::origin($request);
    }

    /// solo dev hay que eliminar despues  ///////////////////
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
    ////////////////////////////////////////////////////////

    public function verifyEmail(Request $request, int $id, string $hash): RedirectResponse
    {
        $customer = Customer::findOrFail($id);
        
        $frontend = rtrim($customer->web ?? '', '/');
        if (! $frontend) {
            abort(404); // el cliente no tiene web registrada
        }

        if (! hash_equals($hash, sha1($customer->getEmailForVerification()))) {
            return redirect("{$frontend}/login?verified=0");
        }
        if (! $customer->hasVerifiedEmail()) {
            $customer->markEmailAsVerified();
        }

        return redirect("{$frontend}/login?verified=1");
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        $web = $this->originWeb($request);
        $customer = $web
            ? Customer::where('email', $request->email)->where('web', $web)->first()
            : null;

        if ($customer && ! $customer->hasVerifiedEmail()) {
            $customer->sendEmailVerificationNotification();
        }
        // Misma respuesta siempre para no revelar qué emails existen
        return response()->json(['message' => 'If the account exists, a verification email was sent.']);
    }


}
