<?php

namespace App\Services;

use App\Models\LessonReservation;
use App\Models\SchoolRequestLessonReservation;

class SchoolRequestLessonReservationService
{
    public function find($id)
    {
        return SchoolRequestLessonReservation::find($id);
    }
}
