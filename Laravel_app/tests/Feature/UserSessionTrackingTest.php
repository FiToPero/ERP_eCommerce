<?php

// Comprueba el historial de sesiones del panel y el cierre de sesiones abandonadas.

namespace Tests\Feature;

use App\Events\PanelLoginCompleted;
use App\Listeners\TrackUserLogin;
use App\Listeners\TrackUserLogout;
use App\Models\User;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserSessionTrackingTest extends TestCase
{
    use RefreshDatabase;

    /** Crea o actualiza una sesión sin duplicarla y sin afectar a otros usuarios. */
    public function test_login_tracking_is_idempotent_for_the_same_session(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $listener = app(TrackUserLogin::class);
        $event = new PanelLoginCompleted($user, 'sesion-uno', '127.0.0.1', 'navegador');
        $listener->handle($event);
        $this->travel(1)->minute();
        $listener->handle($event);
        $listener->handle(new PanelLoginCompleted($other, 'sesion-dos', '127.0.0.1', null));
        $this->assertDatabaseCount('user_sessions', 2);
        $this->assertDatabaseHas('user_sessions', ['user_id' => $user->id, 'session_id' => 'sesion-uno', 'is_active' => true]);
        $this->assertEquals(now()->timestamp, $user->userSessions()->sole()->last_activity_at->timestamp);
    }

    /** El cierre sin usuario o desde la API no modifica sesiones del panel. */
    public function test_logout_ignores_missing_user_and_api_requests(): void
    {
        $user = User::factory()->create();
        app(TrackUserLogin::class)->handle(new PanelLoginCompleted($user, 'sesion-uno', null, null));
        $listener = app(TrackUserLogout::class);
        $listener->handle(new Logout('web', null));
        $this->app['request']->server->set('REQUEST_URI', '/api/auth/logout');
        $listener->handle(new Logout('api', $user));
        $this->assertDatabaseHas('user_sessions', ['user_id' => $user->id, 'is_active' => true, 'logout_at' => null]);
    }

    /** Cierra únicamente sesiones inexistentes o caducadas en el almacenamiento. */
    public function test_close_stale_sessions_preserves_active_sessions(): void
    {
        $user = User::factory()->create();
        $listener = app(TrackUserLogin::class);
        foreach (['activa', 'vencida', 'ausente'] as $id) {
            $listener->handle(new PanelLoginCompleted($user, $id, null, null));
        }
        DB::table('sessions')->insert([
            ['id' => 'activa', 'payload' => '', 'last_activity' => now()->timestamp],
            ['id' => 'vencida', 'payload' => '', 'last_activity' => now()->subMinutes((int) config('session.lifetime') + 1)->timestamp],
        ]);
        $this->artisan('sessions:close-stale')->assertSuccessful();
        $this->assertDatabaseHas('user_sessions', ['session_id' => 'activa', 'is_active' => true, 'logout_at' => null]);
        foreach (['vencida', 'ausente'] as $id) {
            $this->assertDatabaseHas('user_sessions', ['session_id' => $id, 'is_active' => false, 'logout_reason' => 'expired']);
        }
        $this->artisan('sessions:close-stale')->expectsOutput('No stale sessions found.')->assertSuccessful();
    }
}
