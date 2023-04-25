<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassAttendees extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'class_attendees';
    protected $primaryKey = 'id';
    protected $fillable = [
        'class_list_id', 
        'student_id', 
        'created_by', 
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'delete_at'
    ];
    public $timestamps = true;
}
