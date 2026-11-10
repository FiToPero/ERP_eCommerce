<?php

namespace Database\Seeders\FactoriesSeeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionsByRole = [
            1 => range(1, 69),
            2 => [1, 2, 12, 13],
            3 => [...range(1, 49), 54, 55, 56, 57],
            4 => [1, 2, 3, 4, 5, 10, 11, 12, 13, 14, 15, 16, 21, 22, 23, 24, 25, 26, 27, 28, 32, 33, 34, 35, 36, 37, 38, 43, 44, 45],
            5 => [1, 2, 12, 13, 34, 35],
            6 => [1, 2, 10, 11, 12, 13, 22, 23, 24, 25, 26, 27, 33, 34, 35, 36, 37, 38, 43, 44],
        ];

        DB::transaction(function () use ($permissionsByRole): void {
            $permissions = Permission::query()->whereIn('id', range(1, 69))->get()->keyBy('id');

            foreach (range(1, 69) as $permissionId) {
                if (! $permissions->has($permissionId)) {
                    throw new \RuntimeException("No existe el permiso con ID {$permissionId}.");
                }
            }

            foreach ($permissionsByRole as $roleId => $permissionIds) {
                $role = Role::findOrFail($roleId);
                $role->givePermissionTo($permissions->only($permissionIds));
            }
        });
    }
}
