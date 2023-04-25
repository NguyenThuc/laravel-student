<?php

namespace App\Services;

use App\Models\LessonRating;

class LessonRatingService
{
    
    public function store($lessonRating)
    {
        return LessonRating::create($lessonRating);
    }

}