<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ChangePasswordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $seller;

    private $email;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($seller)
    {
        $this->seller = $seller;
        $this->email = $seller->email;

    }//end __construct()


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = $this->email;
        $timeChange = date('Y年m月d日 H:i');
        Mail::send(
            ['text' => 'mail.change-password'],
            [
                'seller'     => $this->seller,
                'timeChange' => $timeChange,
            ],
            function ($message) use ($email) {
                $message->to($email)->subject('【事業者サイト】パスワードが変更されました');
            }
        );

    }//end handle()


}//end class
