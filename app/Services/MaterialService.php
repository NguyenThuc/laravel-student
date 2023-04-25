<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use App\Models\LessonReservation;
use App\Models\MstYear;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\MstTextbookCourse;
use App\Models\MstTextbookLesson;
use App\Models\SchoolRequestLessonReservation;
use DB;

class MaterialService
{
    use SoftDeletes;

    const FIRST_INDEX = '0';

    public function update($data)
    {
        $lessonReservation = LessonReservation::where('id', $data['lessonID'])
            ->where('student_id', $data['studentID'])
            ->first();

        $lessonReservation->mst_textbook_lesson_id = $data['categoryUnit'];
        $lessonReservation->updated_by = $data['updateBy'];

        if (!$lessonReservation->save()) {
            return false;
        }

        if ($lessonReservation->status == LessonReservationService::AFTER_MATCHING_FINISHED_COMPLETED) {
            $bellbirdIds = $this->getBellBirdIdsByTextbookLessonId($lessonReservation->mst_textbook_lesson_id);
            $bellBirdService = new BellbirdApiService();
            $updatedBellbirdMeeting = $bellBirdService->udpateMaterial(
                $lessonReservation->meeting_id,
                $bellbirdIds->bellbird_lesson_id,
                $bellbirdIds->bellbird_course_id,
                $bellbirdIds->bellbird_category_id
            );

            if ($updatedBellbirdMeeting->data->_type == "Error") {
                \Log::error($updatedBellbirdMeeting->data->detail);
                return false;
            }
        }
        
        return $lessonReservation;
    }

    public function convertDateToString($date) {
            $week =  ceil( date( 'j', strtotime($date) ) / 7 );
            $day = date('N', strtotime(date("l", strtotime($date))));

            return "Week ". $week ." Day ". $day;
    }

    public function bookedLessons()
    {
        $tutorId = Session::get("authUserRarejobTutorId");
        $lessonGroups = LessonReservation::where('tutor_id', $tutorId)
            ->with(['masterTextbook'])
            ->get()
            ->groupBy('mst_textbook_id')
            ->toArray();

        $materials = [];
        foreach($lessonGroups as $lessonGroup) {
            foreach($lessonGroup as $index => $lessons) {
                if($index == self::FIRST_INDEX) {
                    $materials[] = [
                        $lessons['master_textbook']['id'],
                        $lessons['master_textbook']['name']
                    ];
                }        
            }
        }

        $lessonGroups = array_values($lessonGroups);

        $groupSet = [];
        foreach($materials as $index => $material) {
            $lessonData = [];
            $lessonGroup = $lessonGroups[$index];

            foreach($lessonGroup as $lessons) {
                $lessonData[] = [
                    "time" => $this->convertDateToString($lessons['lesson_date']),
                    "title" => "[". $this->convertDateToString($lessons['lesson_date']) ."] ".$lessons['master_textbook']['name']
                ];
            }

            $groupSet[] = [
                $material[0], 
                $material[1], 
                $lessonData
            ];
        }

        return $groupSet;
    }

    public function schoolYear()
    {
        return MstYear::all();
    }

    public function filterSchedule($yearId, $textbookId)
    {
        $tutorId = Session::get("authUserRarejobTutorId");
        $resData = LessonReservation::where('tutor_id', $tutorId)
            ->where('mst_textbook_id', $textbookId)
            ->with(['classList', 'masterTextbook'])
            ->get()
            ->toArray();

        $setData = [];
        foreach($resData as $data) {
            $setData[] = [
                "time" => $this->convertDateToString($data['lesson_date']),
                "title" => "[". $this->convertDateToString($data['lesson_date']) ."] ".$data['master_textbook']['name'],
                "grade" => $data['class_list']['mst_year_id']
            ];
        }

        $grade = array_column($setData, 'grade');

        array_multisort($grade, SORT_ASC, $setData);

        return $setData;
    }

    public function category($id) 
    {
        $intialCourseItem = MstTextbookCourse::where('mst_textbook_category_id', $id)->first();
        $textbookCourse = MstTextbookCourse::where('mst_textbook_category_id', $id)->get();
        $textbookLesson = MstTextbookLesson::where('mst_textbook_course_id', $intialCourseItem->id)->get();

        return [
            "textbook_course" => $textbookCourse,
            "textbook_lesson" => $textbookLesson
        ];
    }

    public function getUnitsByCourseId($courseId)
    {
        return MstTextbookLesson::where('mst_textbook_course_id', $courseId)->get();
    }

    public function getMaterialByTextbookLessonId($id){
        $material = MstTextbookLesson::with(['mstTextbookCourse'])->where('id', $id)->first();
        if (!$material) {
            return [];
        }

        $category =  DB::table("mst_textbook_categories")->where('id', $material->mstTextbookCourse->mst_textbook_category_id)->first();
        $lessonUrl = self::generateLessonUrl($material->name ?? $material->name_en, $material->bellbird_lesson_id);

        return [
            "material" => $material,
            "category" => $category,
            "lessonUrl" => $lessonUrl
        ];
    }

    public function getTextBookLessonById($id, $includeCourse = false)
    {
        $textbookLesson = MstTextbookLesson::find($id);

        if (!$textbookLesson) {
            return [];
        }

        $textbookLesson->name = $textbookLesson->name ?? $textbookLesson->name_en;
        $textbookLesson->url = $this->generateLessonUrl($textbookLesson->name, $textbookLesson->bellbird_lesson_id);

        if ($includeCourse) {
            $textbookLesson->course = $textbookLesson->mstTextbookCourse;
            $textbookLesson->course->name = $textbookLesson->course->name ?? $textbookLesson->course->name_en;
        }

        return $textbookLesson;
    }

    public function generateLessonUrl($slug, $master_id)
    {
        $lessonUrl = env('BELLBIRD_LESSON_URL').'/'.$slug.'/'.$master_id;
        return $lessonUrl;
    }

    public function getBellBirdIdsByTextbookLessonId($lessonId)
    {
        return DB::table('mst_textbook_lessons')
            ->select(
                'mst_textbook_lessons.bellbird_lesson_id',
                'mst_textbook_courses.bellbird_course_id',
                'mst_textbook_categories.bellbird_category_id'
            )
            ->leftJoin(
                'mst_textbook_courses',
                'mst_textbook_lessons.mst_textbook_course_id',
                '=',
                'mst_textbook_courses.id'
            )
            ->leftJoin(
                'mst_textbook_categories',
                'mst_textbook_courses.mst_textbook_category_id',
                '=',
                'mst_textbook_categories.id'
            )
            ->where('mst_textbook_lessons.id', $lessonId)
            ->first();
    }
}