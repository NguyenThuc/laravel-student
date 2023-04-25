<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $seller;

    private $urlRedirect;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($seller, $urlRedirect)
    {
        $this->seller = $seller;
        $this->urlRedirect = $urlRedirect;

    }//end __construct()


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $seller = $this->seller;
        $urlRedirect = $this->urlRedirect;

        return $this->subject('【事業者サイト】パスワード再設定メール')->view('mail.forgot-password', compact('seller', 'urlRedirect'));

    }//end build()


}//end class
