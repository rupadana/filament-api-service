<?php

namespace Rupadana\ApiService\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Rupadana\ApiService\Models\Token;

class TokenPolicy
{
    use HandlesAuthorization;

    public function isPolicyEnabled() : bool 
    {
        return config('api-service.models.token.enable_policy', true);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if(!$this->isPolicyEnabled()) {
            return true;
        }

        return $user->can('view_any_token');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Token $token): bool
    {
        if (!$this->isPolicyEnabled()) {
            return true;
        }

        return $user->can('view_token');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if (!$this->isPolicyEnabled()) {
            return true;
        }

        return $user->can('create_token');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Token $token): bool
    {
        if (!$this->isPolicyEnabled()) {
            return true;
        }

        return $user->can('update_token');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Token $token): bool
    {
        if (!$this->isPolicyEnabled()) {
            return true;
        }

        return $user->can('delete_token');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        if (!$this->isPolicyEnabled()) {
            return true;
        }

        return $user->can('delete_any_token');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Token $token): bool
    {
        if (!$this->isPolicyEnabled()) {
            return true;
        }

        return $user->can('force_delete_token');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        if (!$this->isPolicyEnabled()) {
            return true;
        }

        return $user->can('force_delete_any_token');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Token $token): bool
    {
        if (!$this->isPolicyEnabled()) {
            return true;
        }

        return $user->can('restore_token');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        if (!$this->isPolicyEnabled()) {
            return true;
        }

        return $user->can('restore_any_token');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Token $token): bool
    {
        if (!$this->isPolicyEnabled()) {
            return true;
        }

        return $user->can('replicate_token');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        if (!$this->isPolicyEnabled()) {
            return true;
        }

        return $user->can('reorder_token');
    }
}
