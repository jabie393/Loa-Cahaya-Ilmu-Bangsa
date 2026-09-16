<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;
    
    /**
     * Pre-authorization: Hanya role 'ryu_dev' yang diizinkan mengakses & mengatur Role.
     */
    public function before(AuthUser $authUser, string $ability): ?bool
    {
        return $authUser->hasRole('ryu_dev') ? true : false;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('ryu_dev');
    }

    public function view(AuthUser $authUser, Role $role): bool
    {
        return $authUser->hasRole('ryu_dev');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasRole('ryu_dev');
    }

    public function update(AuthUser $authUser, Role $role): bool
    {
        return $authUser->hasRole('ryu_dev');
    }

    public function delete(AuthUser $authUser, Role $role): bool
    {
        return $authUser->hasRole('ryu_dev');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('ryu_dev');
    }

    public function restore(AuthUser $authUser, Role $role): bool
    {
        return $authUser->hasRole('ryu_dev');
    }

    public function forceDelete(AuthUser $authUser, Role $role): bool
    {
        return $authUser->hasRole('ryu_dev');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('ryu_dev');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('ryu_dev');
    }

    public function replicate(AuthUser $authUser, Role $role): bool
    {
        return $authUser->hasRole('ryu_dev');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->hasRole('ryu_dev');
    }

}