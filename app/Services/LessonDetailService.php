<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\SchoolReservationSlot;
use App\Models\LessonReservation;
use App\Models\ClassAttendee;
use App\Models\ClassList;
use App\Models\MstCourse;
use App\Models\MstYear;
use App\Models\MstClass;
use App\Models\Student;
use App\Models\MstTextbookCategory;
use DB;
use App\Models\SchoolRequestLessonReservation;
use App\Models\SchoolReservedSlot;
use App\Models\MstTextbookLesson;
use App\Models\MstTextbookCourse;
use Illuminate\Support\Facades\Log;

class LessonDetailService
{
    private $validTextbookLessoIncludeProps = [
        "mstTextbookCourse",
        "mstTextbookCourse.mstTextbookCategory",
    ];

    public function yearList()
    {
        return MstYear::all();
    }

    public function classList()
    {
        return MstClass::all();
    }

    public function textBookList()
    {
        return MstTextbookCategory::all();
    }

    public function courseList()
    {
        return MstTextbookCourse::all();
    }

    public function lessontextbookList()
    {
        return MstTextbookLesson::all();
    }

    public function materialCourseList($courseId)
    {
        $materialCourse = MstTextbookCourse::where("id", $courseId)->first();
        $materialCourses = MstTextbookCourse::where("mst_textbook_category_id", $materialCourse->mst_textbook_category_id)->get();

        return [
            "categoryId" => $materialCourse->mst_textbook_category_id,
            "courses" => $materialCourses
        ];
    }

    public function getTextbookLessonById($id, $request = null, $include = null)
    {
        $textbookLesson = MstTextbookLesson::find($id);
        $includeProperty = [];

        if(!$textbookLesson) {
            return response()->json([
                'success' => false,
                'message' => 'データが見つかりません'
            ]);

        } 
        
        if($textbookLesson && ($request && $request->include) ||  $include) {
            $include = explode(",", $include ? $include : $request->include);

            foreach($this->validTextbookLessoIncludeProps as $props) {
                if(in_array($props, $include)) {
                    array_push($includeProperty, $props);
                }
            }
        }
        
        return $textbookLesson->load($includeProperty);
    }

    public function getTextbookCourseById($id)
    {
        return MstTextbookCourse::where("id", $id)->first();
    }

    public function getTextbookCategoryById($id)
    {
        return MstTextbookCategory::where("id", $id)->first();
    }

    public function materialTextbookUnitList($textbookId)
    {
        $lessonTextbook = MstTextbookLesson::where("id", $textbookId)->first();
        $textbookLessons = MstTextbookLesson::where("mst_textbook_course_id", $lessonTextbook->mst_textbook_course_id)->get();

        return [
            "courseId" => $lessonTextbook->mst_textbook_course_id,
            "textbooks" => $textbookLessons
        ];
    }

    public function studentList()
    {
        return Student::all();
    }

    public function getLessonsByStatusAndStudentId($status, $id) {
        return 
        LessonReservation::where([
            ['student_id', '=', $id] ,
            ['status', '=', $status] 
        ])->get();
    }
    public function textbookDetails($id)
    {
        return MstTextbook::where('id', $id)->first();
    }

    public function addMinutes($startTime)
    {
        $today = Carbon::createFromFormat('H:i',  $startTime); 
        return $today->addMinutes(30)->format('H:i');
    }

    public function generateDate()
    {
        $startDate = Carbon::now()->format('Y-m-d');
        $endDate = Carbon::now()->addDays(13)->format('Y-m-d');
        $dateRange = CarbonPeriod::create($startDate, $endDate);

        $dates = [];
        foreach ($dateRange as $date) {
            $dates[] = [
                $date->format('D-j'),
                $date->format('Y-m-d')
            ];
        }

        return [
            "today" => $startDate,
            "from" => date('Y年m月d日', strtotime($startDate)),
            "to" => date('m月d日', strtotime($endDate)),
            "dates" => array_chunk($dates, 7)
        ];
    }

    public function classAttendees($classId)
    {
        return ClassAttendee::where('class_list_id', '=', $classId)->with('student')->get();
    }

    public function getAvailableClassAttendees($startTime, $slotDate, $duration, $classId)
    {
        $endTime = date('H:i:s', strtotime($startTime. ' +'. $duration .' minutes'));
        try {
            $studentsIdEnrolled = SchoolReservedSlot::select("lesson_reservations.student_id")
                ->join(
                    "school_reservation_slots",
                    "school_reservation_slots.id",
                    "=",
                    "school_reserved_slots.school_reservation_slot_id"
                )
                ->join(
                    "lesson_reservations",
                    "school_reserved_slots.school_request_lesson_reservation_id",
                    "=",
                    "lesson_reservations.school_request_lesson_reservation_id"
                )
                ->whereBetween("start_time", [ $startTime, $endTime ])
                ->where("reservation_slot_date", $slotDate)
                ->groupBy("student_id")
                ->pluck("student_id")
                ->toArray();

            $students = DB::table('class_attendees')->
                select("class_attendees.*", "students.*")->
                join("students", "class_attendees.student_id", '=', 'students.id')->
                where("class_attendees.class_list_id", "=", $classId)->
                whereNotIn("class_attendees.student_id", array_values(array_unique($studentsIdEnrolled)))->
                whereNull("class_attendees.deleted_at")->
                whereNull("students.deleted_at")->
                get()->toArray();

            return $students;
        } catch (\Exception $e) {
            Log::error($e);
            return array();
        }
    }

    public function materialChange($slot)
    {
        $slot = SchoolReservationSlot::where('id', $slot)->first();
        $bookings = LessonReservation::where('school_reservation_slot_id', $slot->id)
                    ->groupBy('status')
                    ->get()->toArray();
        $textbookDtls = $this->textbookDetails($bookings[0]['mst_textbook_id']);

        return [
            'reservation_slot_date' => $slot->reservation_slot_date,
            'start_time' => $slot->start_time,
            'end_time' => $slot->end_time,
            'mst_textbook_id' => $bookings[0]['mst_textbook_id'],
            'name' => $textbookDtls->name,
            'unit' => $textbookDtls->unit
        ];
    }

    public function lessonDetails($reservationId) 
    {
        $schoolRequest = SchoolRequestLessonReservation::where('id', $reservationId)->first();

        if (!$schoolRequest) {
            return [];
        }

        $requestId = $schoolRequest->id;
        $classId = $schoolRequest->class_list_id;
        $classList = ClassList::where("id", $classId)->first();
        $bookedLessons = LessonReservation::where('school_request_lesson_reservation_id', $requestId)->get();

        $lessonRsvnIds = [];
        $bookedStudents = [];
        foreach($bookedLessons as $lesson) {
            $lessonRsvnIds[] = $lesson->id;
            $bookedStudents[] = $lesson->student_id;
        }

        $attendees = ClassAttendee::select('student_id')
                        ->where('class_list_id', $classId)
                        ->whereNotIn('student_id', $bookedStudents)
                        ->get()
                        ->toArray();

        $studentCandidateLists = Student::whereIn('id', $attendees)->get()->toArray();
        $bookedStudentList = Student::whereIn('id', $bookedStudents)->get()->toArray();

        return [
            "request" => $schoolRequest,
            "booking" => implode(',', $lessonRsvnIds),
            "class" => $classList,
            "candidate" => $studentCandidateLists,
            "booked" => $bookedStudentList,
        ];
    }

    function joinQuery()
    {
        return DB::table("lesson_reservations")
            ->join("school_reservation_slots","lesson_reservations.school_reservation_slot_id","=","school_reservation_slots.id")
            ->join("mst_textbook_lessons","lesson_reservations.mst_textbook_lesson_id","=","mst_textbook_lessons.id");
    }

    public function lessonDetailStudent($student_id)
    {
        $result = self::joinQuery()
            ->select("lesson_reservations.*","school_reservation_slots.*","mst_textbook_lessons.*")
            ->where('lesson_reservations.student_id', '=', $student_id)
            ->orderBy('school_reservation_slots.reservation_slot_date')
            ->limit(2)
            ->get();

        return $result;
    }

    public function lessonDetailStudentWithClass($student_id)
    {
        $result = self::joinQuery()
            ->select("lesson_reservations.*","school_reservation_slots.*","mst_textbook_lessons.*")
            ->where('lesson_reservations.student_id', '=', $student_id)
            ->orderBy('school_reservation_slots.reservation_slot_date')
            ->limit(1)
            ->get();
        return  $result;
    }

    public function convertDateTimeDiffToMinutes($date, $time, $dateTime)
    {
        $reserve_DateTime = date('Y-m-d H:i:s', strtotime("$date $time"));
        $interval= $dateTime->diff($reserve_DateTime);
        $days = $interval->days * config('constants.HOURS_PER_DAY') * config('constants.MINS_PER_HOUR');
        $hours = $interval->h * config('constants.MINS_PER_HOUR');
        $minutes = $interval->i;
        $dateTimeDiff = $days+$hours+$minutes;

        return array(
            'reserve_DateTime' => $reserve_DateTime,
            'dateTimeDifference' => $dateTimeDiff
        );
    }

    function historyQuery()
    {
        return DB::table("lesson_reservations")
            ->join("school_reservation_slots","lesson_reservations.school_reservation_slot_id","=","school_reservation_slots.id")
            ->leftJoin("mst_textbook_lessons","lesson_reservations.mst_textbook_lesson_id","=","mst_textbook_lessons.id")
            ->select("lesson_reservations.*","school_reservation_slots.*","mst_textbook_lessons.*");

    }

    public function historyClass($student_id, $dateNow)
    {
        $result = self::historyQuery()
            ->where('school_reservation_slots.reservation_slot_date', '<=',\DB::raw('"'.$dateNow.'"'))
            ->where('lesson_reservations.student_id', '=', $student_id)
            ->get();
        return $result;
    }

    public function historyIndividual($student_id, $dateNow)
    {
        $result = self::historyQuery()
            ->where('school_reservation_slots.reservation_slot_date', '<=',\DB::raw('"'.$dateNow.'"'))
            ->where('lesson_reservations.student_id', '=', $student_id)
            ->get();
        return $result;
    }

    public function filterByclassList($schoolId)
    {
        $classLists = ClassList::where('school_id', $schoolId)
            ->with(['year:id,name', 'class:id,name'])
            ->get(['mst_year_id', 'mst_class_id']);

        $years = [];
        $classes = [];
        foreach($classLists as $classList) {
            if(!array_key_exists($classList->year->id, $years)) {
                $years[$classList->year->id] = $classList->year->name;
            }
            if(!array_key_exists($classList->class->id, $classes)) {
                $classes[$classList->class->id] = $classList->class->name;
            }
        }

        return [
            "years" => $years,
            "classes" => $classes
        ];
    }
}