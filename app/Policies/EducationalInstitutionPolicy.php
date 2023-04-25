<?php

namespace App\Policies;

use App\Models\EducationalInstitution;
use App\Models\Seller;
use Illuminate\Auth\Access\HandlesAuthorization;

class EducationalInstitutionPolicy
{
    use HandlesAuthorization;


    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Seller $seller)
    {
        //

    }//end viewAny()


    /**
     * Determine whether the user can view the model.
     */
    public function view(Seller $seller, EducationalInstitution $educationalInstitution)
    {
        $sellerId = $seller->id;
        $sellerModel = Seller::find($sellerId);
        if ($sellerModel) {
            $eduInstitutions = $sellerModel->educationalInstitutions->pluck('id')->toArray();
            return in_array($educationalInstitution->id, $eduInstitutions);
        }

        return false;

    }//end view()


    /**
     * Determine whether the user can create models.
     */
    public function create(Seller $seller)
    {

    }//end create()


    /**
     * Determine whether the user can update the model.
     */
    public function update(Seller $seller, EducationalInstitution $educationalInstitution)
    {
        $sellerId = $seller->id;
        $sellerModel = Seller::find($sellerId);
        if ($sellerModel) {
            $eduInstitutions = $sellerModel->educationalInstitutions->pluck('id')->toArray();
            return in_array($educationalInstitution->id, $eduInstitutions);
        }

        return false;

    }//end update()


    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Seller $seller, EducationalInstitution $educationalInstitution)
    {
        //

    }//end delete()


}//end class
