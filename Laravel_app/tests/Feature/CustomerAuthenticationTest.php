<?php

// Comprueba los contratos de autenticación de clientes consumidos por las tiendas.

namespace Tests\Feature;

use App\Models\Customer;
use App\Notifications\ResetCustomerPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CustomerAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private const WEB = 'https://tienda.example';

    /** Crea un cliente de prueba sin depender de las URL configuradas en el entorno. */
    private function customer(array $attributes = []): Customer
    {
        return Customer::create(array_replace([
            'customer_name' => 'Cliente',
            'type' => 'personal',
            'web' => self::WEB,
            'email' => 'cliente@example.com',
            'password' => 'password123',
        ], $attributes));
    }

    /** Registra y verifica un cliente, y rechaza enlaces alterados o vencidos. */
    public function test_registration_and_email_verification(): void
    {
        Notification::fake();
        $this->withHeader('Origin', self::WEB)->postJson('/api/auth/register', [
            'customer_name' => 'Cliente', 'email' => 'cliente@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertCreated()->assertJsonMissingPath('customer.password');

        $customer = Customer::where('email', 'cliente@example.com')->sole();
        $this->assertNull($customer->email_verified_at);
        Notification::assertSentToTimes($customer, VerifyEmail::class, 1);
        $url = Notification::sent($customer, VerifyEmail::class)->first()->toMail($customer)->actionUrl;
        $this->get($url.'&changed=1')->assertForbidden();
        $this->get($url)->assertRedirect(self::WEB.'/login?verified=1');
        $this->assertTrue($customer->fresh()->hasVerifiedEmail());
        $this->get($url)->assertRedirect(self::WEB.'/login?verified=1');

        $expired = URL::temporarySignedRoute('verification.verify', now()->subMinute(), [
            'id' => $customer->id, 'hash' => sha1($customer->email),
        ]);
        $this->get($expired)->assertForbidden();
    }

    /** Rechaza datos inválidos y duplicados en la misma tienda, no en otra. */
    public function test_registration_validates_data_and_email_per_store(): void
    {
        Notification::fake();
        $this->customer();
        $payload = ['customer_name' => 'Otro', 'email' => 'cliente@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123'];

        $this->withHeader('Origin', self::WEB)->postJson('/api/auth/register', $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->withHeader('Origin', 'https://otra.example')->postJson('/api/auth/register', $payload)
            ->assertCreated();
        $this->postJson('/api/auth/register', ['email' => 'invalid', 'password' => 'short'])
            ->assertUnprocessable()->assertJsonValidationErrors(['customer_name', 'email', 'password']);
    }

    /** Mantiene uniforme el reenvío sin notificar a cuentas inexistentes o verificadas. */
    public function test_resend_verification_does_not_disclose_account_state(): void
    {
        Notification::fake();
        $customer = $this->customer();
        $response = $this->withHeader('Origin', self::WEB)
            ->postJson('/api/auth/email/resend', ['email' => $customer->email])->assertOk();
        Notification::assertSentToTimes($customer, VerifyEmail::class, 1);
        $customer->markEmailAsVerified();
        $this->postJson('/api/auth/email/resend', ['email' => $customer->email])
            ->assertOk()->assertExactJson($response->json());
        $this->postJson('/api/auth/email/resend', ['email' => 'missing@example.com'])
            ->assertOk()->assertExactJson($response->json());
        Notification::assertSentToTimes($customer, VerifyEmail::class, 1);
    }

    /** Limita el reenvío y no envía correos a una cuenta de otra tienda. */
    public function test_resend_is_throttled_and_scoped_to_store(): void
    {
        Notification::fake();
        $customer = $this->customer();
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->withHeader('Origin', 'https://otra.example')
                ->postJson('/api/auth/email/resend', ['email' => $customer->email])->assertOk();
        }
        $this->postJson('/api/auth/email/resend', ['email' => $customer->email])->assertTooManyRequests();
        Notification::assertNothingSent();
    }

    /** La firma válida no permite verificar un hash de correo distinto. */
    public function test_verification_rejects_wrong_email_hash(): void
    {
        $customer = $this->customer();
        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $customer->id, 'hash' => sha1('otro@example.com'),
        ]);
        $this->get($url)->assertRedirect(self::WEB.'/login?verified=0');
        $this->assertNull($customer->fresh()->email_verified_at);
    }

    /** Exige credenciales, verificación y origen antes de emitir un token. */
    public function test_login_enforces_credentials_verification_and_origin(): void
    {
        $customer = $this->customer();
        $credentials = ['email' => $customer->email, 'password' => 'password123'];
        $this->withHeader('Origin', self::WEB)->postJson('/api/auth/login', $credentials)->assertForbidden();
        $customer->markEmailAsVerified();
        $this->postJson('/api/auth/login', array_replace($credentials, ['password' => 'incorrect']))
            ->assertUnauthorized();
        $this->withHeader('Origin', 'https://otra.example')->postJson('/api/auth/login', $credentials)
            ->assertUnauthorized();
        $this->withHeader('Origin', self::WEB)->postJson('/api/auth/login', $credentials)
            ->assertOk()->assertJsonStructure(['token', 'token_type', 'customer']);
    }

    /** El segundo inicio invalida el primer token y el logout invalida el actual. */
    public function test_login_replaces_previous_session_and_logout_revokes_token(): void
    {
        $customer = $this->customer();
        $customer->markEmailAsVerified();
        $other = $this->customer(['email' => 'otro@example.com']);
        $other->markEmailAsVerified();
        $credentials = ['email' => $customer->email, 'password' => 'password123'];
        $otherToken = $this->withHeader('Origin', self::WEB)->postJson('/api/auth/login', [
            'email' => $other->email, 'password' => 'password123',
        ])->json('token');
        $first = $this->withHeader('Origin', self::WEB)->postJson('/api/auth/login', $credentials)->json('token');
        $second = $this->postJson('/api/auth/login', $credentials)->json('token');

        $this->assertNotSame($first, $second);
        $this->assertDatabaseHas('customer_sessions', ['customer_id' => $customer->id, 'logout_reason' => 'new_login']);
        $this->withToken($first)->getJson('/api/auth/me')->assertUnauthorized();
        Auth::forgetGuards();
        $this->withToken($second)->getJson('/api/auth/me')->assertOk()->assertJsonPath('id', $customer->id);
        Auth::forgetGuards();
        $this->withToken($second)->postJson('/api/auth/logout')->assertOk();
        $this->assertDatabaseHas('customer_sessions', ['customer_id' => $customer->id, 'logout_reason' => 'logout']);
        Auth::forgetGuards();
        $this->withToken($second)->getJson('/api/auth/me')->assertUnauthorized();
        Auth::forgetGuards();
        $this->withToken($otherToken)->getJson('/api/auth/me')->assertOk()->assertJsonPath('id', $other->id);
    }

    /** Comprueba recuperación, reutilización de token y aislamiento por tienda. */
    public function test_password_reset_is_single_use_and_scoped_to_store(): void
    {
        Notification::fake();
        $customer = $this->customer();
        $customer->markEmailAsVerified();
        $other = $this->customer(['web' => 'https://otra.example']);
        $this->withHeader('Origin', self::WEB)
            ->postJson('/api/auth/forgot-password', ['email' => $customer->email])->assertOk();
        Notification::assertSentTo($customer, ResetCustomerPassword::class);
        Notification::assertNotSentTo($other, ResetCustomerPassword::class);
        $notification = Notification::sent($customer, ResetCustomerPassword::class)->first();
        $this->assertStringStartsWith(self::WEB.'/reset-password?', $notification->toMail($customer)->actionUrl);
        $payload = ['email' => $customer->email, 'token' => $notification->token,
            'password' => 'nuevaclave123', 'password_confirmation' => 'nuevaclave123'];

        $this->withHeader('Origin', 'https://otra.example')->postJson('/api/auth/reset-password', $payload)
            ->assertUnprocessable();
        $this->withHeader('Origin', self::WEB)->postJson('/api/auth/reset-password', $payload)->assertOk();
        $this->assertTrue(Hash::check('nuevaclave123', $customer->fresh()->password));
        $this->assertTrue(Hash::check('password123', $other->fresh()->password));
        $this->postJson('/api/auth/reset-password', $payload)->assertUnprocessable();
        $this->postJson('/api/auth/login', ['email' => $customer->email, 'password' => 'password123'])
            ->assertUnauthorized();
        $this->postJson('/api/auth/login', ['email' => $customer->email, 'password' => 'nuevaclave123'])
            ->assertOk();
    }

    /** Rechaza tokens inválidos, vencidos y solicitudes mal formadas. */
    public function test_password_reset_rejects_invalid_expired_and_unconfirmed_data(): void
    {
        Notification::fake();
        $customer = $this->customer();
        $this->withHeader('Origin', self::WEB)
            ->postJson('/api/auth/forgot-password', ['email' => $customer->email])->assertOk();
        $token = Notification::sent($customer, ResetCustomerPassword::class)->first()->token;
        $payload = ['email' => $customer->email, 'token' => 'invalid',
            'password' => 'nuevaclave123', 'password_confirmation' => 'nuevaclave123'];
        $this->postJson('/api/auth/reset-password', $payload)->assertUnprocessable();
        $this->postJson('/api/auth/reset-password', array_replace($payload, ['password_confirmation' => 'different']))
            ->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->travel(61)->minutes();
        $this->postJson('/api/auth/reset-password', array_replace($payload, ['token' => $token]))
            ->assertUnprocessable();
        $this->assertTrue(Hash::check('password123', $customer->fresh()->password));
    }

    /** Rechaza recuperación sin origen o para un cliente inexistente en la tienda. */
    public function test_password_recovery_requires_matching_origin_and_valid_email(): void
    {
        Notification::fake();
        $customer = $this->customer();
        $this->postJson('/api/auth/forgot-password', ['email' => $customer->email])->assertUnprocessable();
        $this->withHeader('Origin', 'https://otra.example')
            ->postJson('/api/auth/forgot-password', ['email' => $customer->email])->assertUnprocessable();
        $this->postJson('/api/auth/forgot-password', ['email' => 'incorrecto'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        Notification::assertNothingSent();
    }
}
