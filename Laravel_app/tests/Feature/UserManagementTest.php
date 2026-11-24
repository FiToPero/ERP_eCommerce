<?php

// Comprueba la gestión de usuarios, verificación y perfiles Employee en Filament.

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Employee;
use App\Models\User;
use App\Support\UserEmailVerification;
use App\Support\UserRoleAssignments;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Auth\Notifications\VerifyEmail;
use Filament\Facades\Filament;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    /** Prepara el panel y un administrador con permisos explícitos de usuarios. */
    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'manager', 'guard_name' => 'web']);
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        foreach (['ViewAny', 'View', 'Create', 'Update', 'Delete', 'Restore', 'ForceDelete',
            'RestoreAny', 'ForceDeleteAny'] as $action) {
            $this->adminRole->givePermissionTo(Permission::create(['name' => $action.':User', 'guard_name' => 'web']));
        }
        $actor = User::factory()->create();
        $actor->assignRole($this->adminRole);
        $this->actingAs($actor);
    }

    /** Devuelve datos válidos para crear un usuario con su perfil de empleado. */
    private function userData(array $overrides = []): array
    {
        return array_replace([
            'user_name' => 'Empleado', 'email' => 'empleado@example.com',
            'role_id' => $this->adminRole->id, 'password' => 'password123',
            'passwordConfirmation' => 'password123',
            'employee' => [
                'first_name' => 'Ana', 'last_name' => 'Prueba', 'type_identity' => 'DNI',
                'identity_number' => '12345678', 'CUIL' => '20123456789', 'phone' => '123456',
                'position' => 'Ventas', 'address' => 'Calle 1', 'city' => 'Ciudad',
                'state' => 'Estado', 'country' => 'País', 'postal_code' => '1000',
                'marital_status' => 'Single',
            ],
        ], $overrides);
    }

    /** Crea usuario y empleado, asigna el rol y envía un enlace de verificación. */
    public function test_create_user_with_employee_and_verification(): void
    {
        Notification::fake();
        Livewire::test(CreateUser::class)->set('data', $this->userData())
            ->call('create')->assertHasNoFormErrors();
        $user = User::where('email', 'empleado@example.com')->sole();
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertTrue($user->hasRole('admin'));
        $this->assertNull($user->email_verified_at);
        $this->assertSame('Ana', $user->employee->first_name);
        $this->assertTrue($user->employee->user->is($user));
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
    }

    /** Evita altas con datos obligatorios o confirmación de contraseña incorrectos. */
    public function test_create_validates_user_and_employee(): void
    {
        Notification::fake();
        $data = $this->userData(['email' => 'incorrecto', 'passwordConfirmation' => 'incorrecta']);
        $data['employee']['first_name'] = '';
        Livewire::test(CreateUser::class)->set('data', $data)->call('create')
            ->assertHasFormErrors(['email', 'password', 'employee.first_name']);
        $this->assertDatabaseMissing('users', ['user_name' => 'Empleado']);
        $this->assertDatabaseCount('employees', 0);
        Notification::assertNothingSent();
    }

    /** Los identificadores duplicados se rechazan con errores y sin crear registros parciales. */
    public function test_create_rejects_duplicate_user_and_employee_identifiers(): void
    {
        Notification::fake();
        $existing = User::factory()->create(['email' => 'existente@example.com']);
        Employee::factory()->create([
            'user_id' => $existing->id, 'identity_number' => '12345678', 'CUIL' => '20123456789',
        ]);
        Livewire::test(CreateUser::class)
            ->set('data', $this->userData(['email' => 'existente@example.com']))
            ->call('create')
            ->assertHasFormErrors(['email', 'employee.identity_number', 'employee.CUIL']);
        $this->assertDatabaseMissing('users', ['user_name' => 'Empleado']);
        $this->assertDatabaseCount('employees', 1);
        Notification::assertNothingSent();
    }

    /** Un rol superior enviado manualmente se rechaza y no crea al usuario. */
    public function test_create_rejects_forged_superior_role(): void
    {
        Notification::fake();
        $role = Role::where('name', 'super_admin')->sole();
        Livewire::test(CreateUser::class)->set('data', $this->userData(['role_id' => $role->id]))
            ->call('create')->assertHasFormErrors(['role_id']);
        $this->assertDatabaseMissing('users', ['email' => 'empleado@example.com']);
        Notification::assertNothingSent();
    }

    /** Una falla al crear el perfil revierte el usuario y evita el envío de correo. */
    public function test_creation_rolls_back_if_employee_creation_fails(): void
    {
        Notification::fake();
        Event::listen('eloquent.creating: '.Employee::class, fn () => throw new RuntimeException('Fallo al crear empleado'));
        try {
            Livewire::test(CreateUser::class)->set('data', $this->userData())->call('create');
            $this->fail('Se esperaba un fallo al crear el perfil.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Fallo al crear empleado', $exception->getMessage());
        }
        $this->assertDatabaseMissing('users', ['email' => 'empleado@example.com']);
        $this->assertDatabaseCount('employees', 0);
        Notification::assertNothingSent();
    }

    /** Actualiza el usuario y el perfil sin modificar contraseña ni verificación. */
    public function test_edit_user_and_employee_preserves_password_and_verification(): void
    {
        $user = User::factory()->create(['password' => 'password123']);
        $user->assignRole($this->adminRole);
        $employee = Employee::factory()->create(['user_id' => $user->id]);
        $verifiedAt = $user->email_verified_at->toISOString();
        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->set('data.user_name', 'Actualizado')->set('data.employee.first_name', 'María')
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('Actualizado', $user->fresh()->user_name);
        $this->assertSame('María', $employee->fresh()->first_name);
        $this->assertSame($verifiedAt, $user->fresh()->email_verified_at->toISOString());
        $this->assertTrue(Hash::check('password123', $user->fresh()->password));
        $this->assertDatabaseCount('employees', 1);
    }

    /** Confirma el cambio de correo, lo desverifica y envía un enlace al nuevo destino. */
    public function test_email_change_requires_confirmation_and_sends_new_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $user->assignRole($this->adminRole);
        Employee::factory()->create(['user_id' => $user->id]);
        $oldEmail = $user->email;
        $form = Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->set('data.email', 'nuevo@example.com')->call('save')
            ->assertActionMounted('confirmEmailChange');
        $this->assertSame($oldEmail, $user->fresh()->email);
        $form->callMountedAction()->assertHasNoFormErrors();
        $user->refresh();
        $this->assertSame('nuevo@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
        $url = Notification::sent($user, VerifyEmail::class)->first()->toMail($user)->actionUrl;
        $this->get($url)->assertRedirect('/admin/login');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    /** Rechaza una confirmación si otro proceso cambió el correo antes de guardarlo. */
    public function test_email_change_rejects_stale_confirmation(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $user->assignRole($this->adminRole);
        Employee::factory()->create(['user_id' => $user->id]);
        $form = Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->set('data.email', 'nuevo@example.com')->call('save')
            ->assertActionMounted('confirmEmailChange');
        $user->update(['email' => 'concurrente@example.com']);
        $form->callMountedAction();
        $this->assertSame('concurrente@example.com', $user->fresh()->email);
        Notification::assertNothingSent();
        FilamentNotification::assertNotified('No se pudo confirmar el cambio de correo');
    }

    /** Cancelar o confirmar un correo duplicado no modifica la identidad del usuario. */
    public function test_email_change_cancellation_and_duplicate_email(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $user->assignRole($this->adminRole);
        Employee::factory()->create(['user_id' => $user->id]);
        $other = User::factory()->create();
        $original = $user->email;
        $form = Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->set('data.email', $other->email)->call('save')->assertActionMounted('confirmEmailChange');
        $form->call('unmountAction');
        $this->assertSame($original, $user->fresh()->email);
        $form->call('save')->callMountedAction()->assertHasErrors(['data.email' => 'unique']);
        $this->assertSame($original, $user->fresh()->email);
        $this->assertNotNull($user->fresh()->email_verified_at);
        Notification::assertNothingSent();
    }

    /** Un enlace firmado anterior, vencido o modificado no verifica otro correo. */
    public function test_user_verification_rejects_stale_expired_and_modified_links(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        app(UserEmailVerification::class)->send($user);
        $url = Notification::sent($user, VerifyEmail::class)->first()->toMail($user)->actionUrl;
        $this->get($url.'&changed=1')->assertForbidden();
        $user->email = 'nuevo@example.com';
        $user->save();
        $this->get($url)->assertForbidden();
        $expired = URL::temporarySignedRoute('admin-user.verify-email', now()->subMinute(), [
            'id' => $user->id, 'hash' => sha1($user->email),
        ]);
        $this->get($expired)->assertForbidden();
        $this->assertNull($user->fresh()->email_verified_at);
    }

    /** Verificar repetidamente conserva la fecha y aplica el límite de solicitudes. */
    public function test_user_verification_is_idempotent_and_throttled(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        app(UserEmailVerification::class)->send($user);
        $url = Notification::sent($user, VerifyEmail::class)->first()->toMail($user)->actionUrl;
        $this->get($url)->assertRedirect('/admin/login');
        $verifiedAt = $user->fresh()->email_verified_at->toISOString();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->get($url)->assertRedirect('/admin/login');
        }
        $this->get($url)->assertTooManyRequests();
        $this->assertSame($verifiedAt, $user->fresh()->email_verified_at->toISOString());
    }

    /** El reenvío usa el correo guardado y la recuperación requiere acceso al panel. */
    public function test_edit_actions_send_verification_and_password_reset(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        $user->assignRole($this->adminRole);
        Employee::factory()->create(['user_id' => $user->id]);
        $form = Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->callAction('sendVerificationEmail');
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
        $form->callAction('sendPasswordReset');
        Notification::assertSentToTimes($user, ResetPassword::class, 1);
        $user->syncRoles([]);
        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])->callAction('sendPasswordReset');
        FilamentNotification::assertNotified('El usuario no tiene acceso al panel');
        Notification::assertSentToTimes($user, ResetPassword::class, 1);
    }

    /** Informa fallos de transporte sin marcar el correo como verificado. */
    public function test_verification_resend_reports_mail_failure(): void
    {
        $user = User::factory()->unverified()->create();
        $user->assignRole($this->adminRole);
        Employee::factory()->create(['user_id' => $user->id]);
        Event::listen(NotificationSending::class, function (NotificationSending $event): void {
            if ($event->notification instanceof VerifyEmail) {
                throw new RuntimeException('Fallo de correo');
            }
        });
        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])->callAction('sendVerificationEmail');
        FilamentNotification::assertNotified('No se pudo enviar el correo de verificación');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    /** Bloquea la asignación de un rol superior o inválido por un administrador. */
    public function test_role_assignment_rejects_privilege_escalation(): void
    {
        $actor = auth()->user();
        $assignments = app(UserRoleAssignments::class);
        $super = Role::where('name', 'super_admin')->sole();
        $this->assertNotContains('super_admin', $assignments->allowedRoles($actor));
        try {
            $assignments->resolve($actor, $super->id);
            $this->fail('El rol superior no debe poder asignarse.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('data.role_id', $exception->errors());
        }
        $this->expectException(ValidationException::class);
        $assignments->resolve($actor, 'no-es-un-id');
    }

    /** Un administrador no puede gestionar usuarios con roles superiores. */
    public function test_role_hierarchy_blocks_management_of_superior_user(): void
    {
        $target = User::factory()->create();
        $target->assignRole('super_admin');
        $this->assertFalse(app(UserRoleAssignments::class)->canManageUser(auth()->user(), $target));
        $this->assertTrue(app(UserRoleAssignments::class)->canManageUser(auth()->user(), auth()->user()));
    }

    /** La edición también rechaza cambios reales sobre un usuario de mayor jerarquía. */
    public function test_edit_rejects_changes_to_superior_user(): void
    {
        $target = User::factory()->create(['user_name' => 'Original']);
        $target->assignRole('super_admin');
        $employee = Employee::factory()->create(['user_id' => $target->id, 'first_name' => 'Original']);
        Livewire::test(EditUser::class, ['record' => $target->getRouteKey()])
            ->set('data.user_name', 'Modificado')->set('data.role_id', $this->adminRole->id)
            ->set('data.employee.first_name', 'Modificado')
            ->call('save')->assertForbidden();
        $this->assertSame('Original', $target->fresh()->user_name);
        $this->assertSame('Original', $employee->fresh()->first_name);
    }

    /** Deniega acceso al listado y a la creación cuando faltan permisos. */
    public function test_user_resource_requires_permissions(): void
    {
        $actor = User::factory()->create();
        $role = Role::create(['name' => 'sales', 'guard_name' => 'web']);
        $actor->assignRole($role);
        $this->actingAs($actor);
        Livewire::test(ListUsers::class)->assertForbidden();
        Livewire::test(CreateUser::class)->assertForbidden();
    }

    /** Sin permiso de edición o borrado no se pueden modificar registros existentes. */
    public function test_user_update_and_delete_require_permissions(): void
    {
        $target = User::factory()->create();
        $actor = User::factory()->create();
        $role = Role::create(['name' => 'sales', 'guard_name' => 'web']);
        $role->givePermissionTo(Permission::where('name', 'ViewAny:User')->sole());
        $actor->assignRole($role);
        $this->actingAs($actor);
        Livewire::test(EditUser::class, ['record' => $target->getRouteKey()])->assertForbidden();
        Livewire::test(ListUsers::class)->assertTableActionHidden('delete', $target);
        $this->assertNotSoftDeleted($target);
    }

    /** Permite crear el perfil faltante de un usuario existente mediante la edición. */
    public function test_edit_creates_missing_employee_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->adminRole);
        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->set('data.employee', $this->userData()['employee'])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('Ana', $user->fresh()->employee->first_name);
        $this->assertDatabaseCount('employees', 1);
    }

    /** Rechaza identificadores del perfil que pertenecen a otro empleado. */
    public function test_edit_rejects_employee_identifiers_used_by_another_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->adminRole);
        Employee::factory()->create(['user_id' => $user->id]);
        $other = Employee::factory()->create();
        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->set('data.employee.identity_number', $other->identity_number)
            ->set('data.employee.CUIL', $other->CUIL)
            ->call('save')->assertHasFormErrors(['employee.identity_number', 'employee.CUIL']);
        $this->assertNotSame($other->identity_number, $user->employee->fresh()->identity_number);
    }

    /** La baja lógica conserva el perfil; la definitiva elimina el perfil en cascada. */
    public function test_user_delete_restore_and_force_delete_preserve_relationship_rules(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->adminRole);
        $employee = Employee::factory()->create(['user_id' => $user->id]);
        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])->callAction('delete');
        $this->assertSoftDeleted($user);
        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'user_id' => $user->id]);
        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])->callAction('restore');
        $this->assertNotSoftDeleted($user);
        $this->assertSame($employee->id, $user->fresh()->employee->id);
        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])->callAction('delete');
        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])->callAction('forceDelete');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }

    /** Filtra bajas y ejecuta acciones masivas solo sobre los registros seleccionados. */
    public function test_list_filters_and_bulk_actions(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $untouched = User::factory()->create();
        $table = Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords([$first, $second, $untouched])
            ->callTableBulkAction('delete', [$first, $second]);
        $this->assertSoftDeleted($first);
        $this->assertSoftDeleted($second);
        $this->assertNotSoftDeleted($untouched);
        $table->filterTable('trashed', false)->assertCanSeeTableRecords([$first, $second])
            ->assertCanNotSeeTableRecords([$untouched])
            ->callTableBulkAction('restore', [$first]);
        $this->assertNotSoftDeleted($first);
        $table->callTableBulkAction('forceDelete', [$second]);
        $this->assertDatabaseMissing('users', ['id' => $second->id]);
        $this->assertDatabaseHas('users', ['id' => $untouched->id]);
    }

    /** La clave foránea y la unicidad impiden perfiles adicionales o huérfanos. */
    public function test_employee_relations_are_unique_and_require_existing_user(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['user_id' => $user->id]);
        $this->assertTrue($employee->user->is($user));
        $this->assertTrue($user->employee->is($employee));
        try {
            Employee::factory()->create(['user_id' => $user->id]);
            $this->fail('No debe existir más de un perfil para un usuario.');
        } catch (QueryException $exception) {
            $this->assertDatabaseCount('employees', 1);
        }
        $this->expectException(QueryException::class);
        Employee::factory()->create(['user_id' => 999999]);
    }
}
