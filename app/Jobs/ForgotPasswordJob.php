<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $email;

    private $seller;

    private $urlRedirect;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $seller, $urlRedirect)
    {
        $this->email = $email;
        $this->seller = $seller;
        $this->urlRedirect = $urlRedirect;

    }//end __construct()


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = $this->email;

        Mail::send(
            ['text' => 'mail.forgot-password'],
            [
                'seller'      => $this->seller,
                'urlRedirect' => $this->urlRedirect,
            ],
            function ($message) use ($email) {
                $message->to($email)->subject('【事業者サイト】パスワード再設定メール');
            }
        );

    }//end handle()


}//end class
