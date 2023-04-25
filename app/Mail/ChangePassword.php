<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChangePassword extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $seller;

    const SUBJECT = "【事業者サイト】パスワードが変更されました";


    public function __construct($seller)
    {
        $this->seller = $seller;

    }//end __construct()


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $timeChange = date('Y年m月d日 H:i');
        return $this->subject(self::SUBJECT)
            ->view('mail.change-password')
            ->with(['seller' => $this->seller, 'timeChange' => $timeChange]);

    }//end build()


}//end class
