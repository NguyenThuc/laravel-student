<?php

namespace App\Observers;

use App\Models\EducationalStaff;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

class EducationalStaffObserver
{


    /**
     * Handle the EducationalStaff "created" event.
     *
     * @param \App\Models\EducationalStaff $educationalStaff
     *
     * @return void
     */
    public function created(EducationalStaff $educationalStaff)
    {
        ActivityLogService::info('create', Auth::user()->id, 'Create educational staff success');

    }//end created()


    /**
     * Handle the EducationalStaff "updated" event.
     *
     * @param \App\Models\EducationalStaff $educationalStaff
     *
     * @return void
     */
    public function updated(EducationalStaff $educationalStaff)
    {
        ActivityLogService::info('update', Auth::user()->id, 'Update educational staff success', $educationalStaff->getOriginal(), $educationalStaff->getChanges());

    }//end updated()


    /**
     * Handle the EducationalStaff "deleted" event.
     *
     * @param \App\Models\EducationalStaff $educationalStaff
     *
     * @return void
     */
    public function deleted(EducationalStaff $educationalStaff)
    {
        ActivityLogService::info('delete', Auth::user()->id, 'Delete educational staff success');

    }//end deleted()


    /**
     * Handle the EducationalStaff "restored" event.
     *
     * @param \App\Models\EducationalStaff $educationalStaff
     *
     * @return void
     */
    public function restored(EducationalStaff $educationalStaff)
    {
        //

    }//end restored()


    /**
     * Handle the EducationalStaff "force deleted" event.
     *
     * @param \App\Models\EducationalStaff $educationalStaff
     *
     * @return void
     */
    public function forceDeleted(EducationalStaff $educationalStaff)
    {
        //

    }//end forceDeleted()


}//end class
