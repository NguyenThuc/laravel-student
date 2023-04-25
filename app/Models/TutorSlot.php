<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TutorSlot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tutor_id',
        'reservation_slot_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public $timestamps = true;
}