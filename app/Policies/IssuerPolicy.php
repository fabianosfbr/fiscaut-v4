<?php

namespace App\Policies;

use App\Models\Issuer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IssuerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->hasRole('super-admin', 'admin');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Issuer $issuer)
    {
        return $user->hasRole('super-admin', 'admin');
    }

    /**
     * Determine whether the user can create models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->hasRole('super-admin', 'admin');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Issuer $issuer)
    {
        return $user->hasRole('super-admin', 'admin');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Issuer $issuer)
    {
        return $user->hasRole('super-admin', 'admin');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Issuer $issuer)
    {
        return $user->hasRole('super-admin', 'admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Issuer $issuer)
    {
        return $user->hasRole('super-admin', 'admin');
    }
}
