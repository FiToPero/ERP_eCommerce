<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Support\UserEmailVerification;
use App\Support\UserRoleAssignments;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    private ?Role $selectedRole = null;

    protected function beforeCreate(): void
    {
        $this->selectedRole = app(UserRoleAssignments::class)->resolve(
            Filament::auth()->user(),
            $this->data['user']['role_id'] ?? null,
            field: 'data.user.role_id',
        );
    }

    protected function afterCreate(): void
    {
        $user = $this->getRecord()->user()->firstOrFail();
        $user->syncRoles($this->selectedRole);

        if (! $user->hasVerifiedEmail()) {
            DB::afterCommit(fn () => app(UserEmailVerification::class)->sendWithNotification($user));
        }
    }
}
