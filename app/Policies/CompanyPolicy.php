<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Permission;
use App\Models\User;

class CompanyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
        return auth()->user()->permissions()->pluck('id')->contains(Permission::COMPANIES_LIST);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Company $company): bool
    {
        return true;
        return auth()->user()->permissions()->pluck('id')->contains(Permission::COMPANIES_VIEW) && auth()->user()->company_id === $company->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
        return auth()->user()->permissions()->pluck('id')->contains(Permission::COMPANIES_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Company $company): bool
    {
        return true;
        return auth()->user()->permissions()->pluck('id')->contains(Permission::COMPANIES_UPDATE) && auth()->user()->company_id === $company->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Company $company): bool
    {
        return true;
        return auth()->user()->permissions()->pluck('id')->contains(Permission::COMPANIES_DELETE) && auth()->user()->company_id === $company->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Company $company): bool
    {
        return true;
        return auth()->user()->permissions()->pluck('id')->contains(Permission::COMPANIES_RESTORE) && auth()->user()->company_id === $company->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Company $company): bool
    {
        return true;
        return auth()->user()->permissions()->pluck('id')->contains(Permission::COMPANIES_FORCE_DELETE);
    }
}
