<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepartmentPolicy
{
    /**
     * Determine whether the user can view any departments.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view departments');
    }

    /**
     * Determine whether the user can view a specific department.
     */
    public function view(User $user, Department $department): bool
    {
        return $user->hasPermissionTo('view departments');
    }

    /**
     * Determine whether the user can create departments.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create departments');
    }

    /**
     * Determine whether the user can update the department.
     */
    public function update(User $user, Department $department): bool
    {
        return $user->hasPermissionTo('update departments');
    }

    /**
     * Determine whether the user can delete the department.
     */
    public function delete(User $user, Department $department): bool
    {
        return $user->hasPermissionTo('delete departments');
    }

    /**
     * Determine whether the user can update the status of a department.
     */
    public function updateStatus(User $user, Department $department): bool
    {
        return $user->hasPermissionTo('update departments');
    }

    /**
     * Determine whether the user can restore the department.
     */
    public function restore(User $user, Department $department): bool
    {
        return $user->hasPermissionTo('update departments');
    }

    /**
     * Determine whether the user can permanently delete the department.
     */
    public function forceDelete(User $user, Department $department): bool
    {
        return $user->hasPermissionTo('delete departments');
    }
}
