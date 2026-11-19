<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductTableTrashedTest extends TestCase
{
    use RefreshDatabase;

    // public function test_products_include_deleted_records_by_default_and_can_be_filtered(): void
    // {
    //     Filament::setCurrentPanel(Filament::getPanel('admin'));

    //     $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    //     $role->givePermissionTo(Permission::create(['name' => 'ViewAny:Product', 'guard_name' => 'web']));
    //     $user = User::factory()->create();
    //     $user->assignRole($role);
    //     $this->actingAs($user);

    //     $active = Product::factory()->create();
    //     $deleted = Product::factory()->create();
    //     $deleted->delete();

    //     Livewire::test(ListProducts::class)
    //         ->assertCanSeeTableRecords([$active, $deleted])
    //         ->assertCanRenderTableColumn('deleted_at')
    //         ->filterTable('trashed', false)
    //         ->assertCanSeeTableRecords([$deleted])
    //         ->assertCanNotSeeTableRecords([$active])
    //         ->filterTable('trashed', null)
    //         ->assertCanSeeTableRecords([$active])
    //         ->assertCanNotSeeTableRecords([$deleted]);
    // }
}
