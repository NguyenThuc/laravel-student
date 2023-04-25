<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Facades\Cache;

class SchoolService
{
    const SCHOOL_CACHE_KEY = 'school_';
    const PER_PAGE = 100;

    public function find($id)
    {
        $school = Cache::get(self::SCHOOL_CACHE_KEY . $id);

        if (!$school) {
            $school = School::find($id);
            Cache::add('school_' . $id, $school);
        }

        return $school;
    }

    public function details($id)
    {
        return School::where('id', $id)->with(['teacher'])->get();
    }

    public function add($data)
    {
        $school = new School([
            "name" => $data['fld-school-name'],
            "contract_type" => $data['fld-contract-type'],
            "lesson_duration" => $data['fld-lesson-time'],
            "show_paid_guidance" => $data['fld-guide-permission-opt'],
            "representative_teacher_id" => $data['fld-teacher-id'],
            "phone_number" => $data['fld-phone'],
            "mst_textbook_category_ids" => $data['fld-teaching-material']
        ]);

        $school->save();
    }

    public function update($data)
    {
        $school = School::where('id', $data['id'])
        ->update([
            "name" => $data['fld-school-name'],
            "contract_type" => $data['fld-contract-type'],
            "lesson_duration" => $data['fld-lesson-time'],
            "show_paid_guidance" => $data['fld-guide-permission-opt'],
            "representative_teacher_id" => $data['fld-teacher-id'],
            "phone_number" => $data['fld-phone'],
            "mst_textbook_category_ids" => $data['fld-teaching-material']
        ]);

        Cache::forget(self::SCHOOL_CACHE_KEY . $id);

        return $school->save();
    }

    public function delete($id)
    {
        return School::where('id', $id)->delete();
    }
}
