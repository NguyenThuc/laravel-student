<?php

namespace App\Observers;

use App\Models\Seller;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

class SellerObserver
{


    /**
     * Handle the Seller "created" event.
     *
     * @param \App\Models\Seller $seller
     *
     * @return void
     */
    public function created(Seller $seller)
    {
        ActivityLogService::info('create', Auth::user()->id, 'Create seller account success');

    }//end created()


    /**
     * Handle the Seller "updated" event.
     *
     * @param \App\Models\Seller $seller
     *
     * @return void
     */
    public function updated(Seller $seller)
    {
        ActivityLogService::info('update', Auth::user()->id, 'Update seller account success', $seller->getOriginal(), $seller->getChanges());

    }//end updated()


    /**
     * Handle the Seller "deleted" event.
     *
     * @param \App\Models\Seller $seller
     *
     * @return void
     */
    public function deleted(Seller $seller)
    {
        ActivityLogService::info('delete', Auth::user()->id, 'Delete seller account success');

    }//end deleted()


    /**
     * Handle the Seller "restored" event.
     *
     * @param \App\Models\Seller $seller
     *
     * @return void
     */
    public function restored(Seller $seller)
    {
        //

    }//end restored()


    /**
     * Handle the Seller "force deleted" event.
     *
     * @param \App\Models\Seller $seller
     *
     * @return void
     */
    public function forceDeleted(Seller $seller)
    {
        //

    }//end forceDeleted()


}//end class
