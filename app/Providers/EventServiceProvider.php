<?php

namespace App\Providers;

use App\Models\EducationalInstitution;
use App\Models\EducationalStaff;
use App\Models\MstTextbookCourse;
use App\Models\Seller;
use App\Observers\EducationalInstitutionObserver;
use App\Observers\EducationalStaffObserver;
use App\Observers\MstTextbookCourseObserver;
use App\Observers\SellerObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{

    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];


    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Seller::observe(SellerObserver::class);
        EducationalInstitution::observe(EducationalInstitutionObserver::class);
        MstTextbookCourse::observe(MstTextbookCourseObserver::class);
        EducationalStaff::observe(EducationalStaffObserver::class);

    }//end boot()


}//end class
