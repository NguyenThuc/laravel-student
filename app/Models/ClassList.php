<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassList extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'class_lists';
    protected $primaryKey = 'id';
    protected $fillable = [
        'school_id', 
        'mst_course_id', 
        'mst_year_id', 
        'mst_class_id',
        'fiscal_year',
        'created_at',
        'updated_at',
        'delete_at'
    ];
    public $timestamps = true;

    public function course()
    {
        return $this->belongsTo('App\Models\MstCourse', 'mst_course_id');
    }

    public function year()
    {
        return $this->belongsTo('App\Models\MstYear', 'mst_year_id');
    }

    public function class()
    {
        return $this->belongsTo('App\Models\MstClass', 'mst_class_id');
    }
    public function lessonReservation()
    {
        return $this->hasOne(LessonReservation::class);
    }

    public function masterClass()
    {
        return $this->belongsTo(MstClass::class, 'mst_class_id');
    }

    public function masterYear()
    {
        return $this->belongsTo(MstYear::class, 'mst_year_id');
    }
    
}
