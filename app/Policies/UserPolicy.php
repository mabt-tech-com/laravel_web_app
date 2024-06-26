<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
        return auth()->user()->permissions()->pluck('id')->contains(Permission::USERS_LIST);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return true;
        return auth()->user()->permissions()->pluck('id')->contains(Permission::USERS_VIEW) && auth()->user()->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return true;
        return auth()->user()->permissions()->pluck('id')->contains(Permission::USERS_UPDATE) && auth()->user()->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return true;
        return auth()->user()->permissions()->pluck('id')->contains(Permission::USERS_DELETE) && auth()->user()->id === $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return true;
        return auth()->user()->permissions()->pluck('id')->contains(Permission::USERS_RESTORE) && auth()->user()->id === $model->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return true;
        return auth()->user()->permissions()->pluck('id')->contains(Permission::USERS_FORCE_DELETE) && auth()->user()->id === $model->id;
    }
}
