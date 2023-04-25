<?php

namespace App\Policies;

use App\Models\EducationalStaff;
use App\Models\Seller;
use Illuminate\Auth\Access\HandlesAuthorization;

class SellerPolicy
{
    use HandlesAuthorization;

    const ROLE_ADMIN = 1;
    const ROLE_MEMBER = 2;


    /**
     * Determine whether the seller can view the model.
     *
     * @param \App\Models\Seller $user   auth user
     * @param \App\Models\Seller $seller resource object
     *
     * @return bool
     */
    public function view(Seller $user, Seller $seller)
    {
        return $user->isAdmin() || $user->id === $seller->id;

    }//end view()


    /**
     * Determine whether the seller can view any objects of the model.
     *
     * @param \App\Models\Seller $user auth user
     *
     * @return bool
     */
    public function viewAny(Seller $user)
    {
        return $user->isAdmin();

    }//end viewAny()


    /**
     * Determine whether the seller can create models.
     *
     * @param \App\Models\Seller $user auth user
     *
     * @return bool
     */
    public function create(Seller $user)
    {
        return $user->isAdmin();

    }//end create()


    /**
     * Determine whether the seller can update the model.
     *
     * @param \App\Models\Seller $user auth user
     *
     * @return bool
     */
    public function update(Seller $user)
    {
        return $user->isAdmin();

    }//end update()


    /**
     * Determine whether the seller can delete the model.
     *
     * @param \App\Models\Seller $user
     *
     * @return bool
     */
    public function delete(Seller $user)
    {
        return $user->isAdmin();

    }//end delete()


}//end class
