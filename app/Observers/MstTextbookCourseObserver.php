<?php

namespace App\Observers;

use App\Models\MstTextbookCourse;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

class MstTextbookCourseObserver
{


    /**
     * Handle the MstTextbookCourse "created" event.
     *
     * @param \App\Models\MstTextbookCourse $mstTextbookCourse
     *
     * @return void
     */
    public function created(MstTextbookCourse $mstTextbookCourse)
    {
        ActivityLogService::info('create', Auth::user()->id, 'Create mst textbook course success');

    }//end created()


    /**
     * Handle the MstTextbookCourse "updated" event.
     *
     * @param \App\Models\MstTextbookCourse $mstTextbookCourse
     *
     * @return void
     */
    public function updated(MstTextbookCourse $mstTextbookCourse)
    {
        ActivityLogService::info('update', Auth::user()->id, 'Update mst textbook course success', $mstTextbookCourse->getOriginal(), $mstTextbookCourse->getChanges());

    }//end updated()


    /**
     * Handle the MstTextbookCourse "deleted" event.
     *
     * @param \App\Models\MstTextbookCourse $mstTextbookCourse
     *
     * @return void
     */
    public function deleted(MstTextbookCourse $mstTextbookCourse)
    {
        ActivityLogService::info('delete', Auth::user()->id, 'Delete mst textbook course success');

    }//end deleted()


    /**
     * Handle the MstTextbookCourse "restored" event.
     *
     * @param \App\Models\MstTextbookCourse $mstTextbookCourse
     *
     * @return void
     */
    public function restored(MstTextbookCourse $mstTextbookCourse)
    {
        //

    }//end restored()


    /**
     * Handle the MstTextbookCourse "force deleted" event.
     *
     * @param \App\Models\MstTextbookCourse $mstTextbookCourse
     *
     * @return void
     */
    public function forceDeleted(MstTextbookCourse $mstTextbookCourse)
    {
        //

    }//end forceDeleted()


}//end class
