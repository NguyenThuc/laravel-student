<?php

namespace App\Observers;

use App\Models\EducationalInstitution;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

class EducationalInstitutionObserver
{


    /**
     * Handle the EducationalInstitution "created" event.
     *
     * @param \App\Models\EducationalInstitution $educationalInstitution
     *
     * @return void
     */
    public function created(EducationalInstitution $educationalInstitution)
    {
        ActivityLogService::info('create', Auth::user()->id, 'Create educational insitution success');

    }//end created()


    /**
     * Handle the EducationalInstitution "updated" event.
     *
     * @param \App\Models\EducationalInstitution $educationalInstitution
     *
     * @return void
     */
    public function updated(EducationalInstitution $educationalInstitution)
    {
        ActivityLogService::info('update', Auth::user()->id, 'Update educational insitution success', $educationalInstitution->getOriginal(), $educationalInstitution->getChanges());

    }//end updated()


    /**
     * Handle the EducationalInstitution "deleted" event.
     *
     * @param \App\Models\EducationalInstitution $educationalInstitution
     *
     * @return void
     */
    public function deleted(EducationalInstitution $educationalInstitution)
    {
        ActivityLogService::info('delete', Auth::user()->id, 'Delete educational insitution success');

    }//end deleted()


    /**
     * Handle the EducationalInstitution "restored" event.
     *
     * @param \App\Models\EducationalInstitution $educationalInstitution
     *
     * @return void
     */
    public function restored(EducationalInstitution $educationalInstitution)
    {
        //

    }//end restored()


    /**
     * Handle the EducationalInstitution "force deleted" event.
     *
     * @param \App\Models\EducationalInstitution $educationalInstitution
     *
     * @return void
     */
    public function forceDeleted(EducationalInstitution $educationalInstitution)
    {
        //

    }//end forceDeleted()


}//end class
