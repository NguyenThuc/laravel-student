<?php

namespace App\Policies;

use App\Models\EducationalStaff;
use App\Models\Seller;
use Illuminate\Auth\Access\HandlesAuthorization;

class EducationalStaffPolicy
{
    use HandlesAuthorization;


    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\Seller $seller
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(Seller $seller)
    {
        //

    }//end viewAny()


    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\Seller           $seller
     * @param \App\Models\EducationalStaff $educationalStaff
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Seller $seller, EducationalStaff $educationalStaff)
    {
        $educationalInsId = $educationalStaff->educationalInstitution()->first()?->id;
        $educationalInsIds = $seller->educationalInstitutions()->get()->pluck('id')->toArray();

        return $seller->isAdmin() || in_array($educationalInsId, $educationalInsIds);

    }//end view()


    /**
     * Determine whether the user can create models.
     *
     * @param \App\Models\Seller $seller
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Seller $seller)
    {
        //

    }//end create()


    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\Seller           $seller
     * @param \App\Models\EducationalStaff $educationalStaff
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Seller $seller, EducationalStaff $educationalStaff)
    {
        //

    }//end update()


    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\Seller           $seller
     * @param \App\Models\EducationalStaff $educationalStaff
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Seller $seller, EducationalStaff $educationalStaff)
    {
        //

    }//end delete()


    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\Seller           $seller
     * @param \App\Models\EducationalStaff $educationalStaff
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Seller $seller, EducationalStaff $educationalStaff)
    {
        //

    }//end restore()


    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\Seller           $seller
     * @param \App\Models\EducationalStaff $educationalStaff
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Seller $seller, EducationalStaff $educationalStaff)
    {
        //

    }//end forceDelete()


}//end class
