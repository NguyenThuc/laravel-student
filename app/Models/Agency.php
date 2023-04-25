<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    protected $guarded;

    public function educationalInstitutions () {
        return $this->hasMany(EducationalInstitution::class);
    }
}
