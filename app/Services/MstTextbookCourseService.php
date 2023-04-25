<?php

namespace App\Services;

use App\Models\MstTextbookCourse;

class MstTextbookCourseService
{
    public function getCourseByIds($array)
    {
        return MstTextbookCourse::whereIn('id', $array)->get();
    }
} 