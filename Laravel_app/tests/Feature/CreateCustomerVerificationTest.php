<?php

namespace Tests\Feature;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Models\Customer;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Events\RecordCreated;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateCustomerVerificationTest extends TestCase
{
    use RefreshDatabase;

    // protected function setUp(): void
    // {
    //     parent::setUp();

    //     Filament::setCurrentPanel(Filament::getPanel('admin'));

    //     $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    //     foreach (['Create:Customer', 'Update:Customer'] as $permission) {
    //         $role->givePermissionTo(Permission::create(['name' => $permission, 'guard_name' => 'web']));
    //     }
    //     $user = User::factory()->create();
    //     $user->assignRole($role);
    //     $this->actingAs($user);
    // }

    // public function test_customer_is_created_unverified_and_receives_a_working_verification_link(): void
    // {
    //     Notification::fake();

    //     Livewire::test(CreateCustomer::class)
    //         ->set('data', $this->customerData([
    //             'web' => 'https://TIENDA.example:443/',
    //             'email_verified_at' => '2025-01-01 00:00:00',
    //         ]))
    //         ->call('create')
    //         ->assertHasNoFormErrors();

    //     $customer = Customer::where('email', 'cliente@example.com')->sole();
    //     $this->assertSame('https://tienda.example', $customer->web);
    //     $this->assertNull($customer->email_verified_at);
    //     $this->assertNull($customer->profile);
    //     Notification::assertSentToTimes($customer, VerifyEmail::class, 1);
    //     FilamentNotification::assertNotified('Cliente creado y correo de verificación enviado');

    //     $url = Notification::sent($customer, VerifyEmail::class)->first()->toMail($customer)->actionUrl;

    //     $this->withHeader('Origin', $customer->web)
    //         ->postJson('/api/auth/email/resend', ['email' => $customer->email])
    //         ->assertOk();
    //     Notification::assertSentToTimes($customer, VerifyEmail::class, 2);

    //     $this->withHeader('Origin', $customer->web)
    //         ->postJson('/api/auth/login', ['email' => $customer->email, 'password' => 'password'])
    //         ->assertForbidden();

    //     $this->get($url)->assertRedirect('https://tienda.example/login?verified=1');
    //     $this->assertNotNull($customer->fresh()->email_verified_at);

    //     $this->withHeader('Origin', $customer->web)
    //         ->postJson('/api/auth/login', ['email' => $customer->email, 'password' => 'password'])
    //         ->assertOk();

    //     $this->withHeader('Origin', $customer->web)
    //         ->postJson('/api/auth/email/resend', ['email' => $customer->email])
    //         ->assertOk();
    //     Notification::assertSentToTimes($customer, VerifyEmail::class, 2);
    // }

    // public function test_verification_link_expires_and_cannot_be_modified(): void
    // {
    //     Notification::fake();

    //     Livewire::test(CreateCustomer::class)
    //         ->set('data', $this->customerData())
    //         ->call('create')
    //         ->assertHasNoFormErrors();

    //     $customer = Customer::where('email', 'cliente@example.com')->sole();
    //     $url = Notification::sent($customer, VerifyEmail::class)->first()->toMail($customer)->actionUrl;

    //     $this->get($url.'&extra=1')->assertForbidden();
    //     $this->travel(61)->minutes();
    //     $this->get($url)->assertForbidden();
    //     $this->assertNull($customer->fresh()->email_verified_at);
    // }

    // public function test_invalid_web_is_rejected_without_creating_or_sending(): void
    // {
    //     Notification::fake();

    //     foreach (['', 'not-a-url', 'https://tienda.example/path', 'https://tienda.example/?x=1', 'https://user@tienda.example', 'ftp://tienda.example'] as $web) {
    //         Livewire::test(CreateCustomer::class)
    //             ->set('data', $this->customerData(['web' => $web]))
    //             ->call('create')
    //             ->assertHasFormErrors(['web']);
    //     }

    //     $this->assertDatabaseCount('customers', 0);
    //     Notification::assertNothingSent();
    // }

    // public function test_optional_profile_is_validated_and_saved(): void
    // {
    //     Notification::fake();

    //     Livewire::test(CreateCustomer::class)
    //         ->set('data', $this->customerData(['profile_enabled' => true]))
    //         ->call('create')
    //         ->assertHasFormErrors(['profile.cuit', 'profile.phone']);

    //     Livewire::test(CreateCustomer::class)
    //         ->set('data', $this->customerData([
    //             'profile_enabled' => true,
    //             'profile' => ['first_name' => 'Ana', 'cuit' => '12345678901', 'phone' => '123456'],
    //         ]))
    //         ->call('create')
    //         ->assertHasNoFormErrors();

    //     $customer = Customer::where('email', 'cliente@example.com')->sole();
    //     $this->assertSame('Ana', $customer->profile->first_name);
    //     Notification::assertSentToTimes($customer, VerifyEmail::class, 1);
    // }

    // public function test_creating_another_sends_one_email_per_customer(): void
    // {
    //     Notification::fake();

    //     $form = Livewire::test(CreateCustomer::class)
    //         ->set('data', $this->customerData())
    //         ->call('createAnother')
    //         ->assertHasNoFormErrors();

    //     $form->set('data', $this->customerData(['email' => 'otro@example.com', 'web' => 'http://localhost:8080/']))
    //         ->call('create')
    //         ->assertHasNoFormErrors();

    //     $this->assertDatabaseCount('customers', 2);
    //     $this->assertSame('http://localhost:8080', Customer::where('email', 'otro@example.com')->sole()->web);
    //     Notification::assertSentTimes(VerifyEmail::class, 2);
    // }

    // public function test_existing_profile_can_still_be_edited(): void
    // {
    //     $customer = Customer::create($this->customerData());
    //     $customer->profile()->create(['first_name' => 'Ana', 'cuit' => '12345678901', 'phone' => '123456']);

    //     Livewire::test(EditCustomer::class, ['record' => $customer->getRouteKey()])
    //         ->set('data.profile.first_name', 'María')
    //         ->call('save')
    //         ->assertHasNoFormErrors();

    //     $this->assertSame('María', $customer->profile()->first()->first_name);
    // }

    // public function test_mail_failure_keeps_customer_and_shows_error(): void
    // {
    //     Event::listen(NotificationSending::class, function (NotificationSending $event): void {
    //         if ($event->notification instanceof VerifyEmail) {
    //             throw new RuntimeException('Mail transport failed');
    //         }
    //     });

    //     Livewire::test(CreateCustomer::class)
    //         ->set('data', $this->customerData())
    //         ->call('create')
    //         ->assertHasNoFormErrors();

    //     $customer = Customer::where('email', 'cliente@example.com')->sole();
    //     $this->assertNull($customer->email_verified_at);
    //     FilamentNotification::assertNotified('Cliente creado, pero no se pudo enviar el correo de verificación');
    // }

    // public function test_rolled_back_creation_does_not_send_verification(): void
    // {
    //     Notification::fake();
    //     Event::listen(RecordCreated::class, fn () => throw new RuntimeException('Creation failed'));

    //     try {
    //         Livewire::test(CreateCustomer::class)
    //             ->set('data', $this->customerData())
    //             ->call('create');
    //         $this->fail('The creation should have failed.');
    //     } catch (RuntimeException $exception) {
    //         $this->assertSame('Creation failed', $exception->getMessage());
    //     }

    //     $this->assertDatabaseCount('customers', 0);
    //     Notification::assertNothingSent();
    // }

    // private function customerData(array $overrides = []): array
    // {
    //     return array_replace([
    //         'customer_name' => 'Cliente',
    //         'type' => 'personal',
    //         'web' => 'https://tienda.example',
    //         'email' => 'cliente@example.com',
    //         'password' => 'password',
    //         'passwordConfirmation' => 'password',
    //     ], $overrides);
    // }
}
