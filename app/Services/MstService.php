<?php

namespace App\Services;

use App\Models\ClassList;
use App\Models\MstClass;
use App\Models\MstYear;
use App\Models\MstTextbookCategory;
use App\Models\School;

class MstService
{
    public function getClassBySchoolId($schoolId)
    {
        return MstClass::where('school_id', $schoolId)->get();
    }

    public function getYear()
    {
        return MstYear::all();
    }

    public function getByRegisteredClass($schoolId)
    {
        $years = [];
        $classes = [];

        $classList = MstClass::where('school_id', $schoolId)->get();
        $yearList = $this->getYear();

        foreach($classList as $class) {
            $classes[$class->id] = $class->name;
        }

        foreach($yearList as $year) {
            $years[$year->id] = $year->name;
        }

        return [
            "years" => $years,
            "classes" => $classes
        ];
    }

    public function getFirstClassBySchoolId($schoolId)
    {
        return MstClass::where('school_id', $schoolId)->first();
    }

    public function getSchoolYearBySchoolId($schoolId)
    {
        return MstYear::where('school_id', $schoolId)->get();
    }

    public function getTextbookCategoriesBySchoolId($schoolId)
    {
        $schoolInfo = School::find($schoolId);
        if (!$schoolInfo) {
            return [];
        }

        $ids = explode(",", $schoolInfo->mst_textbook_category_ids);

        if (!$ids) {
            return [];
        }

        return MstTextbookCategory::whereIn('id', $ids)->get();
    }

    public function getAllCategories()
    {
        return MstTextbookCategory::with('mstTextbookCourses')->get();
    }
}
