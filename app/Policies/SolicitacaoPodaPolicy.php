<?php

namespace App\Policies;

use App\Models\SolicitacaoPoda;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SolicitacaoPodaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function index(User $user)
    {
        $userPolicy = new UserPolicy();

        return $userPolicy->isAnalistaPodaOrSecretario($user);
    }

    public function requerenteIndex(User $user)
    {
        return $user->role == User::ROLE_ENUM['requerente'];
    }

    public function viewAny(User $user)
    {
        return $this->index($user);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SolicitacaoPoda  $solicitacaoPoda
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return true;
    }

    public function edit(User $user)
    {
        return $this->index($user);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SolicitacaoPoda  $solicitacaoPoda
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function avaliar(User $user)
    {
        return $this->index($user);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SolicitacaoPoda  $solicitacaoPoda
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, SolicitacaoPoda $solicitacaoPoda)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SolicitacaoPoda  $solicitacaoPoda
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, SolicitacaoPoda $solicitacaoPoda)
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SolicitacaoPoda  $solicitacaoPoda
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, SolicitacaoPoda $solicitacaoPoda)
    {
        return false;
    }
}
