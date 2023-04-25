<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CreateSeller extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $seller;

    protected $urlRedirect;


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
        return $this->view('mail.create-seller')->subject('【事業者サイト】新規アカウント発行')->with(
            [
                'name'        => $this->seller->name,
                'urlRedirect' => $this->urlRedirect,
            ]
        );

    }//end build()


}//end class
