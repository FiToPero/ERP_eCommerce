<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ProductChannel;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductChannelPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProductChannel');
    }

    public function view(AuthUser $authUser, ProductChannel $productChannel): bool
    {
        return $authUser->can('View:ProductChannel');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProductChannel');
    }

    public function update(AuthUser $authUser, ProductChannel $productChannel): bool
    {
        return $authUser->can('Update:ProductChannel');
    }

    public function delete(AuthUser $authUser, ProductChannel $productChannel): bool
    {
        return $authUser->can('Delete:ProductChannel');
    }

    public function restore(AuthUser $authUser, ProductChannel $productChannel): bool
    {
        return $authUser->can('Restore:ProductChannel');
    }

    public function forceDelete(AuthUser $authUser, ProductChannel $productChannel): bool
    {
        return $authUser->can('ForceDelete:ProductChannel');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProductChannel');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProductChannel');
    }

    public function replicate(AuthUser $authUser, ProductChannel $productChannel): bool
    {
        return $authUser->can('Replicate:ProductChannel');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProductChannel');
    }

}