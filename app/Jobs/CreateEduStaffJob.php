<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\EducationalStaff;

class CreateEduStaffJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $staff;

    private $password;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(EducationalStaff $staff, $password)
    {
        $this->staff = $staff;
        $this->password = $password;

    }//end __construct()


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $staff = $this->staff;
        Mail::send(
            ['text' => 'mail.create-edu-staff'],
            [
                'staff'    => $staff,
                'password' => $this->password,
            ],
            function ($message) use ($staff) {
                $message->to($staff->email)->subject('【事業者サイト】教育機関スタッフのアカウント発行');
            }
        );

    }//end handle()


}//end class
