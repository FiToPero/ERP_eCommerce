<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserRoleAssignments
{
    private const ASSIGNABLE = [
        'super_admin' => ['super_admin', 'admin', 'manager', 'sales', 'warehouse', 'customer'],
        'admin' => ['admin', 'manager', 'sales', 'warehouse', 'customer'],
        'manager' => ['manager', 'sales', 'warehouse', 'customer'],
    ];

    public function allowedRoles(User $actor): array
    {
        return collect(self::ASSIGNABLE)
            ->filter(fn (array $roles, string $role): bool => $actor->hasRole($role))
            ->flatten()
            ->unique()
            ->values()
            ->all();
    }

    public function canManageUser(User $actor, User $target): bool
    {
        if ($actor->is($target) || $actor->hasRole('super_admin')) {
            return true;
        }

        $allowed = $this->allowedRoles($actor);

        return $allowed !== [] && $target->roles()->get()->every(
            fn (Role $role): bool => $role->guard_name === 'web' && in_array($role->name, $allowed, true),
        );
    }

    public function options(User $actor, ?User $target = null): array
    {
        $names = $this->allowedRoles($actor);

        if ($target && $actor->is($target)) {
            $names = array_merge($names, $target->roles()->where('guard_name', 'web')->pluck('name')->all());
        }

        return Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $names)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function resolve(User $actor, mixed $roleId, ?User $target = null): Role
    {
        if ($target && ! $this->canManageUser($actor, $target)) {
            abort(403);
        }

        if (
            (! is_string($roleId) && ! is_int($roleId))
            || filter_var($roleId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false
        ) {
            throw ValidationException::withMessages(['data.role_id' => 'Selecciona un rol permitido.']);
        }

        $role = Role::query()->whereKey($roleId)->where('guard_name', 'web')->first();

        $isUnchangedOwnRole = $target && $actor->is($target) && $role
            && $target->roles()->count() === 1
            && $target->roles()->whereKey($role->getKey())->exists();

        if (! $role || (! in_array($role->name, $this->allowedRoles($actor), true) && ! $isUnchangedOwnRole)) {
            throw ValidationException::withMessages(['data.role_id' => 'No tienes permiso para asignar este rol.']);
        }

        return $role;
    }
}
