<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class CreatePasswordJob implements ShouldQueue
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
    public function __construct($seller, $urlRedirect)
    {
        $this->seller = $seller;
        $this->email = $seller->email;
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
            ['text' => 'mail.create-seller'],
            [
                'seller'      => $this->seller,
                'urlRedirect' => $this->urlRedirect,
            ],
            function ($message) use ($email) {
                $message->to($email)->subject('【事業者サイト】新規アカウント発行');
            }
        );

    }//end handle()


}//end class
