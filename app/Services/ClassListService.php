<?php

namespace App\Services;

use App\Models\ClassList;
use App\Models\Student;
use App\Models\ClassAttendee;
use App\Models\MstCourse;
use App\Models\MstClass;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use DB;

class ClassListService
{
    const NEW_COURSE = '2';
    const NEW_CLASS = '2';

    private $validIncludeProps = [
        "masterClass",
        "masterYear",
    ];

    function joinQuery()
    {
        return DB::table("class_lists")
        ->join("mst_classes","class_lists.mst_class_id","=","mst_classes.id")
        ->join("mst_years","class_lists.mst_year_id","=","mst_years.id")
        ->leftJoin("students","class_lists.id","=","students.class_list_id");
    }

    public function show($schoolId)
    {
        $result = self::joinQuery()
            ->select("class_lists.*","mst_classes.name as className","mst_years.name as yearName",DB::raw("count(students.id) as student_count"))
            ->where("class_lists.school_id", $schoolId)
            ->whereNull("class_lists.deleted_at")
            ->whereNull("students.deleted_at")
            ->groupBy("class_lists.id")
            ->get();
       
        return $result;
    }

    public function getById($id, $request = null, $include =  null) {
        $classList = ClassList::find($id);
        $includeProperty = [];

        if(!$classList) {
            return response()->json([
                'success' => false,
                'message' => 'データが見つかりません'
            ]);

        } 
        
        if($classList && ($request && $request->include) ||  $include) {
            $include = explode(",", $include ? $include : $request->include);

            foreach($this->validIncludeProps as $props) {
                if(in_array($props, $include)) {
                    array_push($includeProperty, $props);
                }
            }
        }

        return $classList->load($includeProperty);
    }

    public function search($request, $schoolId)
    {
        $year_now = $request->get('year_now');
        $class = $request->get('group');
        $fiscal_year = $request->get('fiscal_year');
        
        return self::joinQuery()
            ->select(
                "class_lists.*",
                "mst_classes.name as className",
                "mst_years.name as yearName",
                DB::raw("count(students.id) as student_count")
            )
            ->when($year_now, function($query, $year_now) {
                $query->where('mst_year_id', $year_now);
            })
            ->when($class, function($query, $class) {
                $query->where('mst_class_id', $class);
            })
            ->when($fiscal_year, function($query, $fiscal_year) {
                $query->where('fiscal_year', $fiscal_year);
            })

            ->where('class_lists.school_id', $schoolId)
            ->whereNull("class_lists.deleted_at")
            ->whereNull("students.deleted_at")
            ->groupBy("class_lists.id")
            ->get();
    }

    public function view($id, $schoolId)
    {
        return Classlist::where('id', $id)
            ->where('school_id', $schoolId)
            ->firstOrFail();
    }

    public function create($data, $schoolId)
    {
        if($data['otherClass']==self::NEW_CLASS){
            $newClass = new MstClass([
                "school_id" => $schoolId,
                "name" => $data['class']
            ]);
            $newClass->save();
            $classId = $newClass->id;
        }else{
            $classId = $data['class'];
        }

        $classSave = new ClassList([
            "school_id" => $schoolId,
            "mst_year_id" => $data['schoolYear'],
            "mst_class_id" => $classId,
            "fiscal_year" => $data['fiscalYear']
        ]);

        if (!$classSave->save()) {
            return false;
        }

        return $classSave;
    }

    public function delete($id)
    {
        return ClassList::where('id', $id)->delete();
    }

    public function getClassStudentCount($id)
    {
        return ClassAttendee::where('class_list_id', $id)
                            ->where('deleted_at', null)->count();
    }

    public function classInfo($id)
    {
        return Classlist::where('id', $id)->with([ 'year', 'class'])->get()->toArray();
    }


    public function deleteStudentInClassAttendee($student_id)
    {
        return ClassAttendee::where('student_id', $student_id)->delete();
    }

    public function checkExistingClass($data)
    {
        return ClassList::where('mst_year_id', $data['schoolYear'])
                            ->where('fiscal_year', $data['fiscalYear'])
                            ->where('mst_class_id', $data['class'])
                            ->exists();
    }

    public function getSchoolLatestClass($schoolId)
    {
        return ClassList::where('school_id', $schoolId)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function getAllBySchoolId($schoolId)
    {
        $classes = ClassList::select('fiscal_year', 'mst_year_id', 'mst_class_id')
            ->where('school_id', $schoolId)
            ->get();

        if ($classes->isEmpty()) {
            return [];
        }

        return $classes;
    }

    public function getGradesOptionsByFiscalYear($schoolId, $fiscalYear)
    {
        $classes = ClassList::select('mst_year_id')
            ->where('school_id', $schoolId)
            ->where('fiscal_year', $fiscalYear)
            ->groupBy('mst_year_id')
            ->orderBy('mst_year_id', 'desc')
            ->get();

        $grades = [];
        foreach ($classes as $class) {
            $grades[] = ['id' => $class->mst_year_id, 'name' => $class->year->name];
        }

        return $grades;
    }

    public function getClassesOptionsByFiscalYearMstYearId($schoolId, $fiscalYear, $mstYearId)
    {
        $classes = ClassList::select('mst_class_id')
            ->where('school_id', $schoolId)
            ->where('fiscal_year', $fiscalYear)
            ->where('mst_year_id', $mstYearId)
            ->groupBy('mst_class_id')
            ->orderBy('mst_class_id', 'desc')
            ->get();

        $classesOptions = [];
        foreach ($classes as $class) {
            $classesOptions[] = ['id' => $class->mst_class_id, 'name' => $class->class->name];
        }

        return $classesOptions;
    }

    public function getClassesOptionByMstYearId($schoolId, $mstYearId)
    {
        $classes = ClassList::select('mst_class_id')
            ->where('school_id', $schoolId)
            ->where('mst_year_id', $mstYearId)
            ->groupBy('mst_class_id')
            ->orderBy('mst_class_id', 'desc')
            ->get();

        $classesOptions = [];
        foreach ($classes as $class) {
            $classesOptions[] = ['id' => $class->mst_class_id, 'name' => $class->class->name];
        }

        return $classesOptions;
    }
}