<?php

namespace Rupadana\ApiService\Tests\Fixtures\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Rupadana\ApiService\Tests\Fixtures\Models\Product;
use Rupadana\ApiService\Tests\Fixtures\Models\User;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\User $user
     */
    public function viewAny(User $user): bool
    {
        dd('sd');

        return $user->can('view_any_product');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User    $user
     * @param \App\Models\Product $product
     */
    public function view(User $user, Product $product): bool
    {
        return $user->can('view_product');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param \App\Models\User $user
     */
    public function create(User $user): bool
    {
        return $user->can('create_product');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User    $user
     * @param \App\Models\Product $product
     */
    public function update(User $user, Product $product): bool
    {
        return $user->can('update_product');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User    $user
     * @param \App\Models\Product $product
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->can('delete_product');
    }

    /**
     * Determine whether the user can bulk delete.
     *
     * @param \App\Models\User $user
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_product');
    }

    /**
     * Determine whether the user can permanently delete.
     *
     * @param \App\Models\User    $user
     * @param \App\Models\Product $product
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->can('force_delete_product');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     *
     * @param \App\Models\User $user
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_product');
    }

    /**
     * Determine whether the user can restore.
     *
     * @param \App\Models\User    $user
     * @param \App\Models\Product $product
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->can('restore_product');
    }

    /**
     * Determine whether the user can bulk restore.
     *
     * @param \App\Models\User $user
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_product');
    }

    /**
     * Determine whether the user can replicate.
     *
     * @param \App\Models\User    $user
     * @param \App\Models\Product $product
     */
    public function replicate(User $user, Product $product): bool
    {
        return $user->can('replicate_product');
    }

    /**
     * Determine whether the user can reorder.
     *
     * @param \App\Models\User $user
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_product');
    }
}
