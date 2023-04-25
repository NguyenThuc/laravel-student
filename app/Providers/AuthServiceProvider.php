<?php

namespace App\Providers;

use App\Policies\EducationalInstitutionPolicy;
use App\Policies\EducationalStaffPolicy;
use App\Policies\SellerPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{

    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [];


    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        $this->setPolicy();

    }//end boot()


    /**
     * Set policy.
     *
     * @return void
     */
    public function setPolicy()
    {
        Gate::resource('sellers', SellerPolicy::class);
        Gate::resource('educational_institutions', EducationalInstitutionPolicy::class);
        Gate::resource('educational_staff', EducationalStaffPolicy::class);

    }//end setPolicy()


}//end class
