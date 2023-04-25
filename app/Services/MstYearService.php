<?php

namespace App\Services;

use App\Models\MstYear;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use DB;

class MstYearService
{
    
    public function showMstYear()
    {
        return MstYear::all();
    }

    public function showYearBySchoolId($schoolId)
    {
        return MstYear::where('school_id', $schoolId)->get();
    }

    public function getById($id)
    {
        return MstYear::find($id);
    }
}