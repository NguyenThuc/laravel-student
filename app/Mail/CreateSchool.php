<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CreateSchool extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $teacher;

    protected $password;


    public function __construct($teacher, $password)
    {
        $this->teacher = $teacher;
        $this->password = $password;

    }//end __construct()


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = [
            'email'    => $this->teacher->email,
            'password' => $this->password,
        ];
        return $this->view('mail.create-teacher')->with('data', $data);

    }//end build()


}//end class
