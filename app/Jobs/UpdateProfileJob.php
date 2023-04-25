<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class UpdateProfileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $email;

    private $seller;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $seller)
    {
        $this->email = $email;
        $this->seller = $seller;

    }//end __construct()


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = $this->email;
        $updatedTime = Carbon::now()->format('Y年m月d日 H:i');

        Mail::send(
            ['text' => 'mail.update-mail-profile'],
            [
                'seller'       => $this->seller,
                'updated_time' => $updatedTime,
            ],
            function ($message) use ($email) {
                $message->to($email)->subject('【事業者サイト】メールアドレスが変更されました');
            }
        );

    }//end handle()


}//end class
