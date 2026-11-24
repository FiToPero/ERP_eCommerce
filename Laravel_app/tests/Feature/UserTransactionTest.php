<?php

// Verifica commits y rollbacks reales del alta de usuario y su correo de verificación.

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Employee;
use App\Models\User;
use Filament\Auth\Notifications\VerifyEmail;
use Filament\Facades\Filament;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Events\RecordCreated;
use Filament\Resources\Events\RecordUpdated;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
class UserTransactionTest extends TestCase
{
    use DatabaseMigrations;

    private Role $role;

    /** Prepara permisos y administrador sin envolver la prueba en una transacción. */
    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        foreach (['ViewAny', 'View', 'Create', 'Update'] as $action) {
            $this->role->givePermissionTo(Permission::create(['name' => $action.':User', 'guard_name' => 'web']));
        }
        $actor = User::factory()->create();
        $actor->assignRole($this->role);
        $this->actingAs($actor);
        $this->assertTrue($actor->can('Create:User'));
    }

    /** Devuelve los datos válidos necesarios para registrar usuario y empleado. */
    private function userData(): array
    {
        return [
            'user_name' => 'Empleado', 'email' => 'empleado@example.com',
            'role_id' => $this->role->id, 'password' => 'password123', 'passwordConfirmation' => 'password123',
            'employee' => [
                'first_name' => 'Ana', 'last_name' => 'Prueba', 'type_identity' => 'DNI',
                'identity_number' => '12345678', 'CUIL' => '20123456789', 'phone' => '123456',
                'position' => 'Ventas', 'address' => 'Calle 1', 'city' => 'Ciudad',
                'state' => 'Estado', 'country' => 'País', 'postal_code' => '1000',
                'marital_status' => 'Single',
            ],
        ];
    }

    /** El correo se envía después del commit y no durante RecordCreated. */
    public function test_verification_is_sent_only_after_real_commit(): void
    {
        Notification::fake();
        $observed = false;
        Event::listen(RecordCreated::class, function () use (&$observed): void {
            $observed = true;
            $this->assertGreaterThan(0, DB::transactionLevel());
            Notification::assertNothingSent();
        });
        Livewire::test(CreateUser::class)->assertOk()->set('data', $this->userData())
            ->call('create')->assertHasNoFormErrors();
        $this->assertTrue($observed);
        $this->assertSame(0, DB::transactionLevel());
        $user = User::where('email', 'empleado@example.com')->sole();
        $this->assertNotNull($user->employee);
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
    }

    /** Un fallo después del alta revierte ambos registros y no envía verificación. */
    public function test_rollback_after_record_created_does_not_send_verification(): void
    {
        Notification::fake();
        Event::listen(RecordCreated::class, fn () => throw new RuntimeException('Fallo antes del commit'));
        try {
            Livewire::test(CreateUser::class)->set('data', $this->userData())->call('create');
            $this->fail('Se esperaba un fallo antes del commit.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Fallo antes del commit', $exception->getMessage());
        }
        $this->assertDatabaseMissing('users', ['email' => 'empleado@example.com']);
        $this->assertDatabaseCount('employees', 0);
        Notification::assertNothingSent();
    }

    /** Si el transporte falla tras el commit, conserva la cuenta e informa el fallo. */
    public function test_mail_failure_after_commit_keeps_user_and_reports_failure(): void
    {
        Event::listen(NotificationSending::class, function (NotificationSending $event): void {
            if ($event->notification instanceof VerifyEmail) {
                throw new RuntimeException('Fallo de correo');
            }
        });
        Livewire::test(CreateUser::class)->set('data', $this->userData())
            ->call('create')->assertHasNoFormErrors();
        $user = User::where('email', 'empleado@example.com')->sole();
        $this->assertNull($user->email_verified_at);
        $this->assertNotNull($user->employee);
        FilamentNotification::assertNotified('No se pudo enviar el correo de verificación');
    }

    /** El cambio de correo y perfil se confirma antes de enviar la verificación. */
    public function test_edit_verification_is_sent_after_real_commit(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $user->assignRole($this->role);
        $employee = Employee::factory()->create(['user_id' => $user->id]);
        $observed = false;
        Event::listen(RecordUpdated::class, function () use (&$observed): void {
            $observed = true;
            $this->assertGreaterThan(0, DB::transactionLevel());
            Notification::assertNothingSent();
        });
        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->set('data.email', 'nuevo@example.com')->set('data.employee.first_name', 'Actualizado')
            ->call('save')->callMountedAction();
        $this->assertTrue($observed);
        $this->assertSame(0, DB::transactionLevel());
        $this->assertSame('Actualizado', $employee->fresh()->first_name);
        $this->assertSame('nuevo@example.com', $user->fresh()->email);
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
    }

    /** Un fallo antes del commit revierte el correo y el perfil sin enviar notificaciones. */
    public function test_edit_rollback_reverts_user_and_employee(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $user->assignRole($this->role);
        $employee = Employee::factory()->create(['user_id' => $user->id, 'first_name' => 'Original']);
        $oldEmail = $user->email;
        Event::listen(RecordUpdated::class, fn () => throw new RuntimeException('Fallo antes del commit'));
        try {
            Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
                ->set('data.email', 'nuevo@example.com')->set('data.employee.first_name', 'Actualizado')
                ->call('save')->callMountedAction();
            $this->fail('Se esperaba un fallo antes del commit.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Fallo antes del commit', $exception->getMessage());
        }
        $this->assertSame($oldEmail, $user->fresh()->email);
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertSame('Original', $employee->fresh()->first_name);
        Notification::assertNothingSent();
    }
}
