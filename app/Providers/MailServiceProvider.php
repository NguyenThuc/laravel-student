<?php

namespace App\Providers;

use App\Mail\CMCTransport;
use App\Mail\DomainFilterPlugin;
use Illuminate\Mail\MailServiceProvider as LaravelMailServiceProvider;

class MailServiceProvider extends LaravelMailServiceProvider
{


    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app['mail.manager']->extend(
            'customers_mail_cloud',
            function () {
                $apiConfig = $this->app['config']->get('api.customers_mail_cloud');
                $CMCTransport = new CMCTransport(
                    $apiConfig['api_key'],
                    $apiConfig['api_user'],
                    $apiConfig['endpoint'],
                );

                $domainFilters = $this->app['config']->get('mail.mailers.customers_mail_cloud.allowed_domains');
                if ($domainFilters) {
                    $CMCTransport->registerPlugin(
                        new DomainFilterPlugin($this->app['config']->get('mail.mailers.customers_mail_cloud.allowed_domains'))
                    );
                }

                return $CMCTransport;
            }
        );

    }//end register()


}//end class
