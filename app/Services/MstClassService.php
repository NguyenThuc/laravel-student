<?php

namespace App\Services;

use App\Models\MstClass;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use DB;

class MstClassService
{
    
    public function showMstClass()
    {
        return MstClass::all();
    }

    public function showClassBySchoolId($schoolId)
    {
        return MstClass::where('school_id', $schoolId)->get();
    }

    public function getById($id)
    {
        return MstClass::find($id);
    } 
}