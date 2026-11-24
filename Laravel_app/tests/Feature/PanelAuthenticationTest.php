<?php

// Comprueba el acceso, cierre y recuperación de contraseña del panel Filament.

namespace Tests\Feature;

use App\Filament\Auth\Login;
use App\Filament\Auth\ResetPassword;
use App\Models\User;
use Filament\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Filament\Facades\Filament;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PanelAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** Selecciona el panel y prepara el rol de acceso para cada prueba. */
    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
    }

    /** Crea un usuario del panel con una contraseña conocida. */
    private function panelUser(): User
    {
        $user = User::factory()->create(['password' => 'password123']);
        $user->assignRole('admin');

        return $user;
    }

    /** Envía el formulario real de login mediante HTTP y conserva la cookie resultante. */
    private function submitLogin(string $email, string $password): TestResponse
    {
        $page = $this->get(Filament::getLoginUrl())->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/wire:snapshot="([^"]+)"/', $page);
        preg_match('/wire:snapshot="([^"]+)"/', $page, $matches);
        $snapshot = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);
        $response = $this->withHeader('X-Livewire', 'true')->postJson('/livewire/update', [
            'components' => [[
                'snapshot' => $snapshot,
                'updates' => ['data.email' => $email, 'data.password' => $password],
                'calls' => [['path' => '', 'method' => 'authenticate', 'params' => []]],
            ]],
        ])->assertOk();
        $this->withCookie(config('session.cookie'), $response->getCookie(config('session.cookie'))->getValue());

        return $response;
    }

    /** Un invitado es redirigido al login y no accede al panel. */
    public function test_guest_cannot_access_panel(): void
    {
        $this->get('/admin')->assertRedirect(Filament::getLoginUrl());
    }

    /** El inicio válido autentica y registra la sesión; el cierre la finaliza. */
    public function test_login_and_logout_track_the_panel_session(): void
    {
        $user = $this->panelUser();
        $this->submitLogin($user->email, 'password123');
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('user_sessions', ['user_id' => $user->id, 'is_active' => true]);
        $logoutSessionId = null;
        Event::listen(Logout::class, function () use (&$logoutSessionId): void {
            $logoutSessionId = request()->hasSession() ? request()->session()->getId() : null;
        });
        $this->post(Filament::getLogoutUrl())->assertRedirect();
        $this->assertSame($user->userSessions()->firstOrFail()->session_id, $logoutSessionId);
        $this->assertGuest();
        $this->assertDatabaseHas('user_sessions', ['user_id' => $user->id, 'is_active' => false, 'logout_reason' => 'logout']);
        $this->get('/admin')->assertRedirect(Filament::getLoginUrl());
    }

    /** Rechaza una contraseña incorrecta y usuarios sin rol de acceso. */
    public function test_login_rejects_bad_credentials_and_user_without_panel_access(): void
    {
        $user = $this->panelUser();
        Livewire::test(Login::class)->set('data.email', $user->email)
            ->set('data.password', 'incorrect')->call('authenticate')->assertHasErrors();
        $this->assertGuest();
        $user->syncRoles([]);
        Livewire::test(Login::class)->set('data.email', $user->email)
            ->set('data.password', 'password123')->call('authenticate')->assertHasErrors();
        $this->assertGuest();
        $this->assertDatabaseMissing('user_sessions', ['user_id' => $user->id]);
    }

    public function test_inactive_user_cannot_log_in_until_reactivated(): void
    {
        $user = $this->panelUser();
        $user->update(['is_active' => false]);
        $this->assertFalse($user->fresh()->is_active);

        Livewire::test(Login::class)->set('data.email', $user->email)
            ->set('data.password', 'password123')->call('authenticate')->assertHasErrors();
        $this->assertGuest();
        $this->assertDatabaseMissing('user_sessions', ['user_id' => $user->id]);

        $user->update(['is_active' => true]);
        $this->submitLogin($user->email, 'password123');
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('user_sessions', ['user_id' => $user->id, 'is_active' => true]);
    }

    /** Limita intentos de login y no autentica mientras el límite está activo. */
    public function test_panel_login_rate_limit_blocks_further_attempts(): void
    {
        $user = $this->panelUser();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            Livewire::test(Login::class)->set('data.email', $user->email)
                ->set('data.password', 'incorrect')->call('authenticate')->assertHasErrors();
        }
        Livewire::test(Login::class)->set('data.email', $user->email)
            ->set('data.password', 'password123')->call('authenticate');
        $this->assertGuest();
        $this->assertDatabaseMissing('user_sessions', ['user_id' => $user->id]);
    }

    /** Comprueba los roles habilitados y denegados por el contrato de acceso al panel. */
    public function test_panel_access_depends_on_allowed_roles(): void
    {
        $user = User::factory()->create();
        $panel = Filament::getPanel('admin');
        $this->assertFalse($user->canAccessPanel($panel));
        foreach (['super_admin', 'manager', 'sales', 'warehouse', 'customer'] as $name) {
            Role::create(['name' => $name, 'guard_name' => 'web']);
        }
        foreach (['super_admin', 'admin', 'manager', 'sales', 'warehouse'] as $name) {
            $user->syncRoles([$name]);
            $this->assertTrue($user->canAccessPanel($panel));
        }
        $user->syncRoles(['customer']);
        $this->assertFalse($user->canAccessPanel($panel));
    }

    /** Un usuario no verificado debe verificar su email antes de acceder al panel. */
    public function test_unverified_user_is_redirected_to_verification(): void
    {
        $user = $this->panelUser();
        $user->forceFill(['email_verified_at' => null])->save();
        $this->actingAs($user)->get('/admin')->assertRedirect(Filament::getEmailVerificationPromptUrl());
        $user->markEmailAsVerified();
        $this->get('/admin')->assertOk();
    }

    /** Solicita el enlace y restablece una contraseña con el token recibido. */
    public function test_password_reset_request_and_successful_reset(): void
    {
        Notification::fake();
        $user = $this->panelUser();
        Livewire::test(RequestPasswordReset::class)
            ->set('data.email', $user->email)->call('request')->assertHasNoErrors();
        Notification::assertSentTo($user, ResetPasswordNotification::class);
        $token = Notification::sent($user, ResetPasswordNotification::class)->first()->token;

        Livewire::test(ResetPassword::class, ['email' => $user->email, 'token' => $token])
            ->set('password', 'nuevaclave123')->set('passwordConfirmation', 'nuevaclave123')
            ->call('resetPassword')->assertHasNoErrors();
        $this->assertTrue(Hash::check('nuevaclave123', $user->fresh()->password));
        $this->assertFalse(Hash::check('password123', $user->fresh()->password));
        $this->assertFalse(Password::broker()->tokenExists($user, $token));
        $this->submitLogin($user->email, 'password123');
        $this->assertGuest();
        $this->submitLogin($user->email, 'nuevaclave123');
        $this->assertAuthenticatedAs($user);
    }

    /** Rechaza tokens inválidos, caducados y cambios con confirmación incorrecta. */
    public function test_password_reset_rejects_invalid_expired_and_unconfirmed_requests(): void
    {
        $user = $this->panelUser();
        $token = Password::broker()->createToken($user);
        Livewire::test(ResetPassword::class, ['email' => $user->email, 'token' => $token])
            ->set('password', 'nuevaclave123')->set('passwordConfirmation', 'diferente')
            ->call('resetPassword')->assertHasErrors();
        $this->travel(61)->minutes();
        Livewire::test(ResetPassword::class, ['email' => $user->email, 'token' => $token])
            ->set('password', 'nuevaclave123')->set('passwordConfirmation', 'nuevaclave123')
            ->call('resetPassword');
        $this->assertTrue(Hash::check('password123', $user->fresh()->password));
    }

    /** Un token incorrecto no modifica la contraseña aunque los datos sean válidos. */
    public function test_password_reset_rejects_an_invalid_token(): void
    {
        $user = $this->panelUser();
        Livewire::test(ResetPassword::class, ['email' => $user->email, 'token' => 'incorrecto'])
            ->set('password', 'nuevaclave123')->set('passwordConfirmation', 'nuevaclave123')
            ->call('resetPassword');
        $this->assertTrue(Hash::check('password123', $user->fresh()->password));
    }

    /** No modifica la contraseña si se revocó el acceso después de emitir el token. */
    public function test_reset_is_denied_when_panel_access_is_revoked(): void
    {
        $user = $this->panelUser();
        $token = Password::broker()->createToken($user);
        $user->syncRoles([]);
        Livewire::test(ResetPassword::class, ['email' => $user->email, 'token' => $token])
            ->set('password', 'nuevaclave123')->set('passwordConfirmation', 'nuevaclave123')
            ->call('resetPassword');
        FilamentNotification::assertNotified('No tienes acceso al panel');
        $this->assertTrue(Hash::check('password123', $user->fresh()->password));
        $this->assertTrue(Password::broker()->tokenExists($user, $token));
    }
}
