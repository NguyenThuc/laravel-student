<?php

namespace App\Services;

use App\Models\MstCourse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use DB;

class MstCourseService
{
    
    public function showMstCourse()
    {
        return MstCourse::all();
    }

    public function showCourseBySchoolId($schoolId)
    {
        return MstCourse::where('school_id', $schoolId)->get();
    }
}