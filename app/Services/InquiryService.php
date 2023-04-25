<?php

namespace App\Services;

use App\Models\Inquiry;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InquiryService
{
    const SENT = 1;
    const NOT_SENT = 2;

    public function create($data, $mailSend)
    {  
        $inquirySave = new Inquiry([
            "student_id" => $data['studentId'],
            "email_content" => $data['inquiry'],
            "status" => $mailSend
        ]);
        if (!$inquirySave->save()) {
            return false;
        }
        return $inquirySave;
    }
   
}