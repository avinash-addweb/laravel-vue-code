<?php

namespace App\Policies;

use App\Models\Bin;
use App\Models\User;
use Auth;
use Illuminate\Auth\Access\Response;

class BinPolicy
{


    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->viewAny();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Bin $bin): bool
    {
        if($user->viewAny()){
            return  true;
        }

        if($bin->clients()->first()){
            return $bin->clients()->first()->user_id==$user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if($user->viewAny()){
            return  $user->can("CREATE-BINS");
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Bin $bin): bool
    {
        if($user->viewAny()){
            return  $user->can("UPDATE-BINS");
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Bin $bin): bool
    {
        if($user->viewAny()){
            return  $user->can("DELETE-BINS");
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Bin $bin): bool
    {
        if($user->viewAny()){
            return  true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Bin $bin): bool
    {
        if($user->viewAny()){
            return  true;
        }
        return false;
    }
}
