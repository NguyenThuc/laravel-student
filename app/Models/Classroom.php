<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $table = 'classrooms';

    public function educationalStaffs() {
        return $this->belongsToMany(EducationalStaff::class, 'educational_staff_classrooms');
    }
}
