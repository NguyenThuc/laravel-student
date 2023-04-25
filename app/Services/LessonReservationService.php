<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\LessonReservation;
use App\Models\ClassList;
use App\Models\MstClass;
use App\Models\MstYear;
use App\Models\SchoolReservationSlot;
use App\Models\LessonConditionLog;
use App\Models\ClassAttendee;
use App\Models\SchoolRequestLessonReservation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use DB;
use App\Models\MstTextbookLesson;
use App\Models\School;
use DateTime;
use DateInterval;
use DatePeriod;
use App\Models\SchoolReservedSlot;
use App\Models\MstTextbookCategory;


class LessonReservationService
{
    const NO_REPEAT = '1';
    const WEEKLY = '2';
    const MONTHLY = '3';
    const NO_DATA_FOUND = 'データが見つかりません';
    const ONGOING_STATUS = 2;
    const COMPLETED_STATUS = 5;
    const DISPLAY_LIMIT = 1;
    const TUTOR_STATUS_STAND_BY ='1';
    const BOOKING_SINGLE = '1';
    const BOOKING_MULTIPLE = '2';
    const FIFTEEN_MINUTES = '15';
    const TWENTY_FIVE_MINUTES = '25';
    const INITIAL_ID = 1;
    const BEFORE_MATCHING = 1;
    const AFTER_MATCHING_FINISHED_COMPLETED = 2;
    const CANCELLED = 3;
    const ON_GOING = 4;
    const DATE_TIME_12HOUR_FORMAT = 'Y-m-d h:i a';
    const DATE_TIME_24HOUR_FORMAT = 'Y-m-d H:i';
    const TIME_12HOUR_FORMAT = 'h:i a';
    const MYSQL_DATE_TIME_FORMAT = 'Y-m-d h:i:s';
    const DATE_FORMAT = 'Y-m-d';
    const STANDBY = 1;
    const TEN_MINUTES = "10 minutes";
    const HOUR_FORMAT = 'H:i:s';

    private $validIncludeProps = [
        "schoolReservationSlot",
        "classList",
        "lessonConditionLogs",
        "teacher",
        "masterTextbook"
    ];

    function getDateForSpecificDayBetweenDates($startDate, $endDate, $day_number) 
    {
        $day = config('constants.'.$day_number);
        $endDate = strtotime($endDate);
        
        for($i = strtotime($day, strtotime($startDate)); $i <= $endDate; $i = strtotime('+1 week', $i)) {
            $date_array[] = date('Y-m-d',$i);
        }
        
        return $date_array;
    }

    public function reservationDates($type, $fromDate, $toDate)
    {
        $dayInWord = Carbon::createFromFormat('Y-m-d', $fromDate)->format('l');

        if($type == self::NO_REPEAT) {

            return array($fromDate);

        } else if($type == self::WEEKLY) {

            $startDate = Carbon::parse($fromDate);
            $endDate = Carbon::parse($toDate);
        
            for ($date = $startDate; $date->lte($endDate); $date->addWeek()) {
                $reserveDate[] = $date->format('Y-m-d');
            }
            
            return $reserveDate;

        } else if($type == self::MONTHLY) {

            $target_date = new DateTime($fromDate);
            $until_date = new DateTime($toDate);

            $target_day = $target_date->format("w");
            foreach (config('constants.ORDINAL') as $o) {
                $check_date = date("Y-m-d", strtotime($o . " " . config('constants.'.$target_day) . " of " . $target_date->format("Y-m")));
                if ($check_date == $target_date->format("Y-m-d")) {
                    $check_ord = $o;
                    break;
                }
            }

            $booking_dates = [];
            $check_month = new DateTime($target_date->format("Y-m-01"));
            do {
                $check_date = new DateTime(date("Y-m-d", strtotime($check_ord . " " . config('constants.'.$target_day) . " of " . $check_month->format("Y-m"))));
                if ($check_date->format("Y-m") == $check_month->format("Y-m") && $check_date <= $until_date) {
                    $booking_dates[] = $check_date->format("Y-m-d");
                }

                $check_month = $check_month->modify("next month");
            } while ($check_month <= $until_date);

            return $booking_dates;
        } 
    }

    public function checkClass($schoolYear, $classId, $fiscalYear) 
    {
        $class = ClassList::where('mst_year_id', '=', $schoolYear)
            ->where('mst_class_id', '=', $classId)
            ->where('fiscal_year', '=', $fiscalYear)
            ->first();

        $studentLists = [];
        if(empty($class)) {
            return [
                "class_id" => 0,
                "students" => 0
            ];;
        } 

        $classStudents = ClassAttendee::with('student')
        ->where('class_list_id', '=', $class->id)
        ->orderBy('id', 'asc')
        ->get();

        foreach($classStudents as $studentInfo) {
            $studentLists[] = [
                'id' => $studentInfo->id,
                'rarejob' => $studentInfo->student[0]['rarejob_student_id'],
                'attendance_id' => $studentInfo->student[0]['attendance_id']
            ];
        }

        return [
            "class_id" => $class->id,
            "students" => $studentLists
        ];
    }

    public function reservationDtls($id)
    {
        $srlr = DB::table("school_reserved_slots")
            ->join(
                "school_request_lesson_reservations",
                "school_request_lesson_reservations.id",
                "=",
                "school_reserved_slots.school_request_lesson_reservation_id"
            )->join(
                "school_reservation_slots",
                "school_reserved_slots.school_reservation_slot_id",
                "=",
                "school_reservation_slots.id"
            )->select(
                "school_request_lesson_reservations.id",
                "school_request_lesson_reservations.school_id",
                "school_request_lesson_reservations.class_list_id",
                "school_request_lesson_reservations.mst_textbook_lesson_id",
                "school_request_lesson_reservations.id as school_request_lesson_reservations_id",
                "school_request_lesson_reservations.start_time",
                "school_request_lesson_reservations.duration",
                "school_request_lesson_reservations.created_by",
                "school_reserved_slots.reserved_slot_count"
            )->where("school_request_lesson_reservations.id","=",$id)
            ->first();

        if (!$srlr) {
            return [];
        }
    
        $materialId = $srlr->mst_textbook_lesson_id;
        $classListId = $srlr->class_list_id;
        $class = ClassList::with(['year', 'class'])->where('id', $classListId)->first();
        $material = MstTextbookLesson::with(['mstTextbookCourse'])->where('id', $materialId)->first();

        $category = null;

        if (isset($material->mstTextbookCourse->mst_textbook_category_id)){
            $category =  DB::table("mst_textbook_categories")->where('id', $material->mstTextbookCourse->mst_textbook_category_id)->first();
        }
        
        // $count = SchoolReservedSlot::where('school_reservation_slot_id',$id)->first();
        $school = School::where('id',$class->school_id)->first();
        
        $duration = $srlr->duration * 60;
        $endTime = strtotime($srlr->start_time) + $duration;
        $endTime = date(self::TIME_12HOUR_FORMAT, $endTime);
        $startTime = date(self::TIME_12HOUR_FORMAT, strtotime($srlr->start_time));
        $bookDate = date(self::DATE_FORMAT, strtotime($srlr->start_time));
        $day = date('N', strtotime($bookDate));
        if ($day == 1) {
            $day = config('constants.JP_MON');
        } else if ($day == 2) {
            $day = config('constants.JP_TUE');
        } else if ($day == 3) {
            $day = config('constants.JP_WED');
        } else if ($day == 4) {
            $day = config('constants.JP_THU');
        } else if ($day == 5) {
            $day = config('constants.JP_FRI');
        } else if ($day == 6) {
            $day = config('constants.JP_SAT');
        } else if ($day == 7) {
            $day = config('constants.JP_SUN');
        }

        return [
            "srlr" => $srlr,
            "class" => $class,
            "material" => $material,
            "count" => $srlr->reserved_slot_count,
            "bookDate" => $bookDate,
            "startTime" => $startTime,
            "endTime" => $endTime,
            "day" => $day,
            "school" => $school->name,
            "category" => $category->name ?? $category->name_en ?? null
        ];
    }

    public function before($data)
    {
        try {
            DB::beginTransaction();

            $bookingIds = explode(",", $data['resBook']);
            $lessonBookingDtls = $this->bookingRegistration($data);
            $countDuplicate = count($lessonBookingDtls['rsvn_duplicate']);
            if($countDuplicate > 0) {
                $reservationDates = array_diff($lessonBookingDtls['rsvn_dates'], $lessonBookingDtls['rsvn_duplicate']);
                array_splice($reservationDates, $lessonBookingDtls['rsvn_per_student'], $lessonBookingDtls['rsvn_to_deny']);
            } else {
                $reservationDates = $lessonBookingDtls['rsvn_allowed'];
            }
    
            foreach($reservationDates as $reservationDate) {
                foreach($lessonBookingDtls['rsvn_students'] as $student) {
                    $bookingSet = $this->getSlotInfo($data['resDate'], $data['resTime'], $data['resDuration'], $data);
                    foreach($bookingSet as $booking) {
                        $bookings[] = $booking;
                    }
                }
            }
    
            LessonReservation::insert($bookings);
            LessonReservation::whereIn("id", $bookingIds)->forceDelete();
            
            $noOfBokingMade = count($bookings);

            DB::commit();

            if($noOfBokingMade > 1) {
                $bookType = self::BOOKING_MULTIPLE;
            } else {
                $bookType = self::BOOKING_SINGLE;
            }

            if($countDuplicate > 0) {
                $filteredDates = array_diff($lessonBookingDtls['rsvn_dates'], $lessonBookingDtls['rsvn_duplicate']);;
                return response()->json([
                    "status" => true,
                    "data" => array(
                        "Allowed" => array_splice($reservationDates, 0, $lessonBookingDtls['rsvn_per_student']),
                        "Denied" => array_splice($filteredDates, $lessonBookingDtls['rsvn_per_student'], $lessonBookingDtls['rsvn_to_deny']),
                        "Type" => $bookType
                    )
                ]);
            } else {
                return response()->json([
                    "status" => true,
                    "data" => array(
                        "Allowed" => $lessonBookingDtls['rsvn_allowed'],
                        "Denied" => $lessonBookingDtls['rsvn_denied'],
                        "Type" => $bookType
                    )
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function after($data)
    {
        $lesson = LessonReservation::where('id', $data['resId'])
            ->update([
                "mst_textbook_id" => $data['resMaterial'],
                "memo" => $data['resMemo']
            ]);

        return $lesson;
    }

    public function reserve($data)
    {
        try {
            DB::beginTransaction();

            $lessonBookingDtls = $this->bookingRegistration($data);
            $countDuplicate = count($lessonBookingDtls['rsvn_duplicate']);
            if($countDuplicate > 0) {
                $reservationDates = array_diff($lessonBookingDtls['rsvn_dates'], $lessonBookingDtls['rsvn_duplicate']);
                array_splice($reservationDates, $lessonBookingDtls['rsvn_per_student'], $lessonBookingDtls['rsvn_to_deny']);
            } else {
                $reservationDates = $lessonBookingDtls['rsvn_allowed'];
            }

            $bookings = [];
            foreach($reservationDates as $reservationDate) {
                foreach($lessonBookingDtls['rsvn_students'] as $student) {

                    $bookingSet = $this->getSlotInfo($data['resDate'], $data['resTime'], $data['resDuration'], $data);
                    foreach($bookingSet as $booking) {
                        $bookings[] = $booking;
                    }

                }
            }

            LessonReservation::insert($bookings);

            $noOfBokingMade = count($bookings);
            
            DB::commit();

            if($noOfBokingMade > 1) {
                $bookType = self::BOOKING_MULTIPLE;
            } else {
                $bookType = self::BOOKING_SINGLE;
            }

            if($countDuplicate > 0) {
                $filteredDates = array_diff($lessonBookingDtls['rsvn_dates'], $lessonBookingDtls['rsvn_duplicate']);;
                return response()->json([
                    "status" => true,
                    "data" => array(
                        "Allowed" => array_splice($reservationDates, 0, $lessonBookingDtls['rsvn_per_student']),
                        "Denied" => array_splice($filteredDates, $lessonBookingDtls['rsvn_per_student'], $lessonBookingDtls['rsvn_to_deny']),
                        "Type" => $bookType
                    )
                ]);
            } else {
                return response()->json([
                    "status" => true,
                    "data" => array(
                        "Allowed" => $lessonBookingDtls['rsvn_allowed'],
                        "Denied" => $lessonBookingDtls['rsvn_denied'],
                        "Type" => $bookType
                    )
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function deleteByReservationSlotId($id)
    {
        DB::beginTransaction();
        try {
            $teacherId = auth()->guard()->user()->id;
            $srlr = SchoolRequestLessonReservation::find($id);
            $query = DB::table('school_request_lesson_reservations')
                ->join(
                    "lesson_reservations",
                    "school_request_lesson_reservations.id",
                    "=",
                    "lesson_reservations.school_request_lesson_reservation_id"
                )
                ->where('school_request_lesson_reservations.id', $id);

            if ($srlr->status == self::BEFORE_MATCHING) {
                $today = date('Y-m-d H:i:s');
                $query->update(
                    [
                        'lesson_reservations.deleted_at' => $today,
                        'school_request_lesson_reservations.deleted_at' => $today,
                        'lesson_reservations.deleted_by' => $teacherId,
                        'school_request_lesson_reservations.deleted_by' => $teacherId,
                        'lesson_reservations.status' => self::CANCELLED,
                        'school_request_lesson_reservations.status' => self::CANCELLED
                    ]
                );
            } else {
                $query->update(
                    [
                        'lesson_reservations.status' => self::CANCELLED,
                        'school_request_lesson_reservations.status' => self::CANCELLED,
                        'lesson_reservations.updated_by' => $teacherId,
                        'school_request_lesson_reservations.updated_by' => $teacherId
                    ]
                );
            }

            SchoolReservedSlot::where('school_request_lesson_reservation_id', $id)->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollback();
            return false;
        }
    }

    public function create($data)
    {
        $lesson = new LessonReservation([
            "lesson_start_time" => $data['lesson_start_time'],
            "lesson_end_time" => $data['lesson_end_time'],
        ]);

        return $lesson->save();
    }

    public function exists($data)
    {
        return LessonReservation::where('lesson_start_time', '<', $data["lesson_end_time"])
        ->where('lesson_start_time', '>', $data["lesson_start_time"])
        ->where(DB::raw("(STR_TO_DATE(lesson_start_time,'%Y-%m-%d'))"), Carbon::parse($data["lesson_end_time"])->format('Y-m-d'))
        ->exists();
    }

    public function delete($id)
    {
        return LessonReservation::where('id', $id)->delete();
    }

    public function getAll()
    {
        return LessonReservation::all();
    }

    public function getById($id, $request, $include = null, $studentId = null)
    {
        $lessonReservation = $studentId ? LessonReservation::where("id", $id)->where("student_id", $studentId)->first()
            : LessonReservation::find($id);
        $includeProperty = [];

        if(!$lessonReservation) {
            return response()->json([
                'success' => false,
                'message' => self::NO_DATA_FOUND
            ]);

        } 
        
        if($lessonReservation && ($request && $request->include) ||  $include) {
            $include = explode(",", $include ? $include : $request->include);

            foreach($this->validIncludeProps as $props) {
                if(in_array($props, $include)) {
                    array_push($includeProperty, $props);
                }
            }
        }

        $lessonReservation['success'] = true;
        return json_decode($lessonReservation->load($includeProperty));
    }

    function joinQuery()
    {
        return DB::table("school_request_lesson_reservations")
        ->join("class_lists","school_request_lesson_reservations.class_list_id","=","class_lists.id")
        ->join("school_reserved_slots","school_request_lesson_reservations.id","=","school_reserved_slots.school_request_lesson_reservation_id")
        ->leftJoin("mst_textbook_lessons","school_request_lesson_reservations.mst_textbook_lesson_id","=","mst_textbook_lessons.id")
        ->join("mst_classes","class_lists.mst_class_id","=","mst_classes.id")
        ->join("mst_years","class_lists.mst_year_id","=","mst_years.id");
    }
    
    public function show($schoolId, $sort)
    {
        $currentDate = date(self::MYSQL_DATE_TIME_FORMAT);
      
        $lessonReservations = self::joinQuery()
            ->select(
                "school_request_lesson_reservations.id as reservationId",
                "school_request_lesson_reservations.school_id",
                "school_request_lesson_reservations.start_time",
                "school_request_lesson_reservations.class_list_id",
                "school_request_lesson_reservations.memo",
                "school_request_lesson_reservations.status",
                "school_request_lesson_reservations.duration",
                "mst_years.name as yearName",
                DB::raw("IFNULL(mst_textbook_lessons.name, mst_textbook_lessons.name_en) as textbookName"),
                "school_request_lesson_reservations.created_at as createdDate",
                "school_request_lesson_reservations.updated_at as updatedDate",
                "mst_classes.name as class_name",
                "class_lists.mst_year_id",
                "class_lists.mst_class_id",
                "class_lists.fiscal_year",
                "school_request_lesson_reservations.created_by",
                "school_reserved_slots.school_reservation_slot_id",
                DB::raw("school_reserved_slots.reserved_slot_count as student_count"))
            ->where("school_request_lesson_reservations.school_id", "=", $schoolId)
            ->where("school_request_lesson_reservations.status", "!=", self::CANCELLED)
            ->whereNull("school_request_lesson_reservations.deleted_at")
            ->groupBy("school_request_lesson_reservations.id")
            ->orderBy('school_request_lesson_reservations.start_time', $sort)
            ->get();
        return self::schoolRequestLessonReservationForList($lessonReservations);
    }
    public function refineSearch($request, $schoolId, $sort)
    {
        $schoolYear = $request->get('fld-schoolYear');
        $class = $request->get('fld-class');
        $status = $request->get('fld-status');
        if ($status == self::ON_GOING){
            $status = self::AFTER_MATCHING_FINISHED_COMPLETED;
        }
        $reservationId = $request->get('fld-id');
        $person = $request->get('fld-person');
        $dateRange = str_replace(" ", "", $request->get('daterange'));
        
        $string = explode('-',$dateRange);
        $finalDate1 = $string[0].'-'.$string[1].'-'.$string[2];
        $finalDate2 = $string[3].'-'.$string[4].'-'.$string[5];
        $startTime ='00:00:00';
        $endTime ='23:59:59';
        $startDate = date( self::DATE_FORMAT, strtotime($finalDate1));
        $endDate = date(self::DATE_FORMAT, strtotime($finalDate2));
        
        $lessonReservations = self::joinQuery()
        ->select(
            "school_request_lesson_reservations.id as reservationId",
            "school_request_lesson_reservations.school_id",
            "school_request_lesson_reservations.start_time",
            "school_request_lesson_reservations.class_list_id",
            "school_request_lesson_reservations.memo",
            "school_request_lesson_reservations.status",
            "school_request_lesson_reservations.duration",
            "mst_years.name as yearName",
            DB::raw("IFNULL(mst_textbook_lessons.name, mst_textbook_lessons.name_en) as textbookName"),
            "school_request_lesson_reservations.created_at as createdDate",
            "school_request_lesson_reservations.updated_at as updatedDate",
            "mst_classes.name as class_name",
            "class_lists.mst_year_id",
            "class_lists.mst_class_id",
            "class_lists.fiscal_year",
            "school_request_lesson_reservations.created_by",
            "school_reserved_slots.school_reservation_slot_id",
            DB::raw("school_reserved_slots.reserved_slot_count as student_count"))
            ->where("school_request_lesson_reservations.school_id","=",$schoolId)
            ->when($schoolYear, function($query, $schoolYear) {
                $query->where('mst_years.id', $schoolYear);
            })
            ->when($class, function($query, $class) {
                $query->where('mst_classes.id', $class);
            })
            ->when($status, function($query, $status) {
                $query->where('school_request_lesson_reservations.status', $status);
            })
            ->when($reservationId, function($query, $reservationId) {
                $query->where('school_request_lesson_reservations.id', $reservationId);
            })
            ->when($person, function($query, $person) {
                $query->where('school_request_lesson_reservations.created_by', $person);
            })
            ->when($startDate, function($query) use($startDate, $endDate, $startTime, $endTime) {
                $query->whereBetween('school_request_lesson_reservations.start_time', [$startDate.' '.$startTime, $endDate.' '.$endTime]);
            })
            ->where("school_request_lesson_reservations.status", "!=", self::CANCELLED)
            ->whereNull("school_request_lesson_reservations.deleted_at")
            ->groupBy('school_request_lesson_reservations.id')
            ->orderBy('school_request_lesson_reservations.start_time', $sort)
            ->get();
            if ($request->get('fld-status') == self::ON_GOING){
                return self::schoolRequestLessonReservationForOngoing($lessonReservations);
            }
            return self::schoolRequestLessonReservationForAfterAndBeforeMatching($lessonReservations);
    }
    function schoolRequestLessonReservationArray($list, $startTime, $endTime, $dateTimeNow)
    {
        $day = date('N', strtotime($startTime));
        if ($day == 1) {
            $day = config('constants.JP_MON');
        } else if ($day == 2) {
            $day = config('constants.JP_TUE');
        } else if ($day == 3) {
            $day = config('constants.JP_WED');
        } else if ($day == 4) {
            $day = config('constants.JP_THU');
        } else if ($day == 5) {
            $day = config('constants.JP_FRI');
        } else if ($day == 6) {
            $day = config('constants.JP_SAT');
        } else if ($day == 7) {
            $day = config('constants.JP_SUN');
        }

        $data = [ "id" => $list->reservationId,
                "school_id" => $list->school_id,
                "class_list_id" => $list->class_list_id,
                "start_time" => $startTime,
                "end_time" => $endTime,
                "dateTimeNow" => $dateTimeNow,
                "duration" => $list->duration,
                "memo" => $list->memo,
                "status" => $list->status,
                "yearName" => $list->yearName,
                "textbookName" => $list->textbookName,
                "createdDate" => $list->createdDate,
                "updatedDate" => $list->updatedDate,
                "class_name" => $list->class_name,
                "mst_year_id" => $list->mst_year_id,
                "mst_class_id" => $list->mst_class_id,
                "fiscal_year" => $list->fiscal_year,
                "student_count" => $list->student_count,
                "created_by" => $list->created_by,
                "day" => $day,
                "school_reservation_slot_id" => $list->school_reservation_slot_id
            ]; 
        return $data;
    }
    function generateDatetimeStarttimeEndtime($list)
    {
            $duration = $list->duration * 60;
            $dateTimeNow = date(self::DATE_TIME_24HOUR_FORMAT);
            $startTime = $list->start_time;
            $startTime = strtotime($startTime);
            $startTime = date(self::DATE_TIME_24HOUR_FORMAT, $startTime);
            $endTime = strtotime($startTime) + $duration;
            $endTime = date(self::DATE_TIME_24HOUR_FORMAT, $endTime);  
           
        return ["dateTimeNow" => $dateTimeNow, "startTime" => $startTime, "endTime" => $endTime];
    }
    function schoolRequestLessonReservationForList($fromQuery)
    {
        $data =[];
        foreach ($fromQuery as $list){
            $generateDatetimeStarttimeEndtime = self::generateDatetimeStarttimeEndtime($list);
            if (strtotime($generateDatetimeStarttimeEndtime['dateTimeNow']) < strtotime($generateDatetimeStarttimeEndtime['endTime'])){
                $data []= self::schoolRequestLessonReservationArray($list, $generateDatetimeStarttimeEndtime['startTime'], $generateDatetimeStarttimeEndtime['endTime'], $generateDatetimeStarttimeEndtime['dateTimeNow']);
            }
        }
        return $data;
    }
    function schoolRequestLessonReservationForAfterAndBeforeMatching($fromQuery)
    {
        $data =[];
        foreach ($fromQuery as $list){
            $generateDatetimeStarttimeEndtime = self::generateDatetimeStarttimeEndtime($list);
            if (strtotime($generateDatetimeStarttimeEndtime['dateTimeNow']) < strtotime($generateDatetimeStarttimeEndtime['startTime']) && strtotime($generateDatetimeStarttimeEndtime['dateTimeNow']) < strtotime($generateDatetimeStarttimeEndtime['endTime'])){
                $data []= self::schoolRequestLessonReservationArray($list, $generateDatetimeStarttimeEndtime['startTime'], $generateDatetimeStarttimeEndtime['endTime'], $generateDatetimeStarttimeEndtime['dateTimeNow']);
            }
        }
        return $data;
    }
    function schoolRequestLessonReservationForOngoing($fromQuery)
    {
        $data =[];
        foreach ($fromQuery as $list){
            $generateDatetimeStarttimeEndtime = self::generateDatetimeStarttimeEndtime($list);
            if ($list->status == self::AFTER_MATCHING_FINISHED_COMPLETED  && strtotime($generateDatetimeStarttimeEndtime['dateTimeNow']) > strtotime($generateDatetimeStarttimeEndtime['startTime']) && strtotime($generateDatetimeStarttimeEndtime['dateTimeNow']) < strtotime($generateDatetimeStarttimeEndtime['endTime'])){
                $data []= self::schoolRequestLessonReservationArray($list, $generateDatetimeStarttimeEndtime['startTime'], $generateDatetimeStarttimeEndtime['endTime'], $generateDatetimeStarttimeEndtime['dateTimeNow']);
            }
        }
        return $data;
    }

    public function getReservationsToMatch()
    {
        $targetReservationDate = date(
            'Y-m-d',
            strtotime('+' . env('DAYS_BEFORE_LESSON_MATCHING') . ' days')
        );

        $reservationSlots = SchoolReservationSlot::where('reservation_slot_date', '<=', $targetReservationDate)->get();
        if ($reservationSlots->isEmpty()) {
            return [];
        }

        $slotIds = [];
        foreach ($reservationSlots as $reservationSlot) {
            $slotIds[] = $reservationSlot->id;
        }

        return LessonReservation::whereIn('school_reservation_slot_id', $slotIds)
            ->where('tutor_id', 0)
            ->get();
    }

    public function getReservationsToReassign()
    {
        $gracePeriodMinutes = (int) env('MINUTES_BEFORE_TUTOR_REASSIGNMENT');
        $gracePeriodSec = time() + ($gracePeriodMinutes * 60);
        $gracePeriodDate = date('Y-m-d H:i:s', $gracePeriodSec);
        $explodedDateTime = explode(' ', $gracePeriodDate);

        $slots = SchoolReservationSlot::where('reservation_slot_date', $explodedDateTime[0])
            ->where('start_time' >= $explodedDateTime[1])
            ->get();

        if ($slots->isEmpty()) {
            return [];
        }

        $slotIds = [];
        foreach ($slots as $slot) {
            $slotIds[] = $slot->id;
        }

        return LessonReservation::whereIn('school_reservation_slot_id', $slotIds)->get();
    }

    public function getReservation($id)
    {
        return LessonReservation::where('id', $id)->first();
    }

    public function updateTutorStatus($id, $tutorStatus)
    {
        return LessonReservation::where('id', $id)
            ->update(array('tutor_status' => $tutorStatus));
    }

    public function searchByEMSEventId($emsEventId)
    {     
        return LessonReservation::select('id', 'class_list_id')
            ->where('ems_event_id', $emsEventId)
            ->first();
    }

    public function getTutorStatus($eventId)
    {  
        $eventExist = DB::table('lesson_reservations')->where('ems_event_id', $eventId)->exists();
        
        if ($eventExist) {
            $tutor =  DB::table('lesson_reservations')
            ->where('ems_event_id', $eventId)
            ->select('tutor_status')
            ->first();
        
            $statusId = $tutor->tutor_status;
            return  ['tutor_status' =>   $statusId];
        }

        return  ['tutor_status' => self::TUTOR_STATUS_STAND_BY];
    }

    public function addBellbirdMeetingDetails($event_id, $meeting_id, $meeting_password, $meeting_hostkey)
    {
        $lesson = LessonReservation::where('ems_event_id', $event_id)
            ->update([
                "meeting_id" => $meeting_id,
                "meeting_password" => $meeting_password,
                "meeting_hostkey" => $meeting_hostkey
            ]);

        return $lesson;
    }

    public function bookingRegistration($data) 
    {
        $slotId = $data['resSlot'];
        $slotDtls = SchoolReservationSlot::where('id', $slotId)->first();

        $numberOfSlot = $slotDtls->remaining_slot_count;

        $listOfStudent = $data['resStudent'][0];
        $numberOfStudent = count($listOfStudent);

        $rsvnType = $data['resType'];
        $rsvnDate = $data['resDate'];
        $rsvnDeadline = $data['resDeadline'];
        $rsvnMaterial = $data['resMaterial'];
        $reservationDates = $this->reservationDates($rsvnType, $rsvnDate, $rsvnDeadline);
        $reservationDatesRef = $this->reservationDates($rsvnType, $rsvnDate, $rsvnDeadline);

        $numberOfReservation = count($reservationDates);
        $allowedRsvnPerStud = (int)floor($numberOfSlot / $numberOfStudent);
        $datesToDeny = (int)$numberOfReservation - $allowedRsvnPerStud;

        array_splice($reservationDates, $allowedRsvnPerStud, $datesToDeny);

        $duplicateLessonBooking = [];
        foreach($listOfStudent as $student) {
            foreach($reservationDates as $date) {
                $checkBooking = LessonReservation::where("school_reservation_slot_id", $slotId)
                    ->where("student_id", $student)
                    ->where("mst_textbook_id", $rsvnMaterial)
                    ->first();

                if($checkBooking) {
                    $duplicateLessonBooking[] = $date;
                }
            }
        }

        return [
            "rsvn_slot_id" => $slotDtls->id,
            "rsvn_students" => $listOfStudent,
            "rsvn_per_student" => $allowedRsvnPerStud,
            "rsvn_to_deny" => $datesToDeny,
            "rsvn_dates" => $reservationDatesRef,
            "rsvn_allowed" => array_splice($reservationDates, 0, $allowedRsvnPerStud),
            "rsvn_denied" => array_splice($reservationDatesRef, $allowedRsvnPerStud, $datesToDeny),
            "rsvn_duplicate" => array_unique($duplicateLessonBooking)
        ];
    }

    public function getInitialStudents($schoolId, $count)
    {
        $fiscalYear = date('Y');
        $schoolYear = MstYear::where('id', '=', self::INITIAL_ID)->first();
        $schoolClass = MstClass::where('school_id', '=', $schoolId)->first();

        $class = ClassList::where('mst_year_id', '=', $schoolYear->id)
            ->where('mst_class_id', '=', $schoolClass->id)
            ->where('fiscal_year', '=', $fiscalYear)
            ->first();
        
        $initialStudents = [];
        if(empty($class)) {
            return $initialStudents;
        } 

        $studentLists = ClassAttendee::with('student')
        ->where('class_list_id', '=', $class->id)
        ->orderBy('id', 'desc')
        ->get();

        foreach($studentLists as $studentInfo) {
            $initialStudents[] = [
                'id' => $studentInfo->id,
                'rarejob' => $studentInfo->student[0]['rarejob_student_id'],
                'attendance_id' => $studentInfo->student[0]['attendance_id']
            ];
        }

        return $initialStudents;
    }

    public function getSlotInfo($date, $timeStart, $time, $data)
    {
        $timeEnd = '00:00:00';
        $timeFormat = 'H:i:s';

        $datetimeStart = new DateTime($date . " " . $timeStart);
        if ($time == self::FIFTEEN_MINUTES){
            $timeEnd =  date($timeFormat, (strtotime($datetimeStart->format($timeFormat) . ' + 20 minutes')));
        }

        if ($time == self::TWENTY_FIVE_MINUTES){
            $timeEnd =  date($timeFormat, (strtotime($datetimeStart->format($timeFormat) . ' + 30 minutes')));
        }

        $datetimeEnd =  new DateTime($date . " " . $timeEnd);

        $interval = DateInterval::createFromDateString('10 minutes');
        $period = new DatePeriod($datetimeStart, $interval, $datetimeEnd);

        $bookings = [];
        foreach ($period as $dt) {
            $date = $dt->format('Y-m-d');
            $timeStart = $dt->format($timeFormat);
            $timeEnd =  date($timeFormat, strtotime($dt->format($timeFormat) . ' + 10 minutes'));

            $schoolSlotInfo = SchoolReservationSlot::where("reservation_slot_date", $date)
                ->where("start_time", $timeStart)
                ->where("end_time", $timeEnd)
                ->first();

            if ($schoolSlotInfo) {
                foreach($data['resStudent'] as $student) {
                    foreach($student as $studentId) {
                        $todayDate = Carbon::now();
                        $bookings[] = [
                            "school_reservation_slot_id" => $schoolSlotInfo->id,
                            "duration" => $data['resDuration'],
                            "student_id" => $studentId,
                            "class_list_id" => $data['resClass'],
                            "tutor_id" => $data['resTutor'],
                            "mst_textbook_id" => $data['resMaterial'],
                            "memo" => $data['resMemo'],
                            "status" => $data['resStatus'],
                            "updated_by" => $data['resUser'],
                            "created_at" => $todayDate,
                            "updated_at" => $todayDate
                        ];
                    }
                }
            }

        }

        return $bookings;
    }

    public function getAvailableSlot($date, $timeStart, $duration)
    {
        $timeEnd = '00:00:00';
        $timeFormat = 'H:i:s';

        $datetimeStart = new DateTime($date . " " . $timeStart);
        if ($duration == self::FIFTEEN_MINUTES){
            $timeEnd =  date($timeFormat, (strtotime($datetimeStart->format($timeFormat) . ' + 20 minutes')));
        }

        if ($duration == self::TWENTY_FIVE_MINUTES){
            $timeEnd =  date($timeFormat, (strtotime($datetimeStart->format($timeFormat) . ' + 30 minutes')));
        }

        $datetimeEnd =  new DateTime($date . " " . $timeEnd);

        $interval = DateInterval::createFromDateString('10 minutes');
        $period = new DatePeriod($datetimeStart, $interval, $datetimeEnd);

        $queryString = [];
        foreach ($period as $dt) {
            $date = $dt->format('Y-m-d');
            $timeStart = $dt->format($timeFormat);
            $timeEnd =  date($timeFormat, strtotime($dt->format($timeFormat) . ' + 10 minutes'));

            $schoolSlotInfo = SchoolReservationSlot::where("reservation_slot_date", $date)
                ->where("start_time", $timeStart)
                ->where("end_time", $timeEnd)
                ->first();

            if ($schoolSlotInfo) {
                $queryString[] = [
                    "slot_id" => $schoolSlotInfo->id,
                    "slot_date" => $schoolSlotInfo->reservation_slot_date,
                    "slot_time_start" => $schoolSlotInfo->start_time
                ];
            }

        }

        return $queryString;
    }

    public function doBooking($data) {
        try {
            $now = date("Y-m-d H:i:s");
            $reservation_dates = $this->reservationDates($data['resType'], $data['resDate'], $data['resDeadline']);
            foreach($reservation_dates as $r_date) {
                $schedule = $r_date . " " . $data['resTime'];
                $duration = $data['resDuration'];
                $student_count = count($data['resStudent'][0]);
                if (!$this->checkRemainingSlotCount($schedule, $duration, $student_count)) {
                    continue;
                }
                $slots = $this->getAvailableSlot($r_date, $data['resTime'], $duration);

                DB::beginTransaction();
                $srLessonReservation = new SchoolRequestLessonReservation;
                $srLessonReservation->school_id = $data['resSchoolId'];
                $srLessonReservation->class_list_id = $data['resClass'];
                $srLessonReservation->start_time = $schedule;
                $srLessonReservation->duration = $data['resDuration'];
                $srLessonReservation->mst_textbook_lesson_id = $data['resUnit'];
                $srLessonReservation->memo = $data['resMemo'];
                $srLessonReservation->status = $data['resStatus'];
                $srLessonReservation->created_by = $data['resTeacherId'];
                $srLessonReservation->save();
                $requestLessonReservationId = $srLessonReservation->id;

                $reservedSlotQuery = [];
                foreach($slots as $slot) {
                    $reservedSlotQuery[] = [
                        "school_request_lesson_reservation_id" => $requestLessonReservationId,
                        "school_reservation_slot_id" => $slot['slot_id'],
                        "reserved_slot_count" => $student_count,
                        "created_at" => $now,
                        "created_by" => $data['resTeacherId']
                    ];
                }
                SchoolReservedSlot::insert($reservedSlotQuery);
                //after insert check if remaining slot count is >= 0
                if (!$this->checkRemainingSlotCount($schedule, $duration, 0)) {
                    DB::rollback();
                    continue;
                }

                $lessonRequestQuery = [];
                foreach($data['resStudent'][0] as $student) {
                    $lessonRequestQuery[] = [
                        "school_request_lesson_reservation_id" => $requestLessonReservationId,
                        "student_id" => $student,
                        "tutor_id" => 0,
                        "memo" => $data['resMemo'],
                        "status" => $data['resStatus'],
                        "created_at" => $now,
                        "created_by" => $data['resTeacherId']
                    ];
                }

                LessonReservation::insert($lessonRequestQuery);
                $success_dates[] = $r_date;
                DB::commit();
            }

            $failed_dates = array_diff($reservation_dates, $success_dates);

            if(count($reservation_dates) > 1) {
                $bookType = self::BOOKING_MULTIPLE;
            } else {
                $bookType = self::BOOKING_SINGLE;
            }

            return response()->json([
                "status" => true,
                "data" => array(
                    "Allowed" => $success_dates,
                    "Denied" => $failed_dates,
                    "Type" => $bookType
                )
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return false;
        }
    }

    public function doUpdateBookig($data)
    {
        DB::beginTransaction();
        try {
            $requestId = $data['requestID'];
            $textbookLessonId = $data['resUnit'];
            $memo = $data['resMemo'];
            $updateBy = $data['updateBy'];

            $srlr = SchoolRequestLessonReservation::where("id", $requestId)->first();
            $srlr->mst_textbook_lesson_id = $textbookLessonId;
            $srlr->memo = $memo;
            $srlr->updated_by = $updateBy;
            $srlr->save();

            if ($srlr->status == self::AFTER_MATCHING_FINISHED_COMPLETED) {
                $materialService = new MaterialService();
                $bellbirdService = new BellbirdApiService();
                $bellbirdIds = $materialService->getBellBirdIdsByTextbookLessonId($textbookLessonId);

                $lessonReservations = LessonReservation::select('meeting_id')
                    ->where('school_request_lesson_reservation_id', $srlr->id)
                    ->whereNull('mst_textbook_lesson_id')
                    ->get();

                foreach ($lessonReservations as $lessonReservation) {
                    $updatedBellbirdMaterial = $bellbirdService->udpateMaterial(
                        $lessonReservation->meeting_id,
                        $bellbirdIds->bellbird_lesson_id,
                        $bellbirdIds->bellbird_course_id,
                        $bellbirdIds->bellbird_category_id
                    );

                    if ($updatedBellbirdMaterial->data->_type == "Error") {
                        \Log::error($updatedBellbirdMaterial->data->detail);
                        continue;
                    }
                }
            }

            $noOfBokingMade = self::STANDBY;
            if($noOfBokingMade > 1) {
                $bookType = self::BOOKING_MULTIPLE;
            } else {
                $bookType = self::BOOKING_SINGLE;
            }

            DB::commit();
            return response()->json([
                "status" => true,
                "data" => array(
                    "Allowed" => "",
                    "Denied" => "",
                    "Type" => $bookType
                )
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        } 
    }

    public function doCancellationBooking($schoolRequestLessonReservationId)
    {
        SchoolRequestLessonReservation::where("school_request_lesson_reservation_id", $schoolRequestLessonReservationId)->delete();
        LessonReservation::where("school_request_lesson_reservation_id", $schoolRequestLessonReservationId)->delete();
        SchoolReservedSlot::where("school_request_lesson_reservation_id", $schoolRequestLessonReservationId)->delete(); 
    }


    public function getByMeetingId($meetingId)
    {
        return LessonReservation::where('meeting_id', $meetingId)->first();
    }

    public function checkRemainingSlotCount($datetime, $duration, $students)
    {
        $duration = $duration + 5;
        $startDatetime = new \DateTime($datetime); 
        $endDatetime = new \DateTime(date(self::DATE_TIME_24HOUR_FORMAT, strtotime("$datetime + $duration minutes"))); 

        $interval = \DateInterval::createFromDateString(self::TEN_MINUTES);
        $period = new \DatePeriod($startDatetime, $interval, $endDatetime);
    
        foreach ($period as $dt) {
            $startTime = $dt->format(self::HOUR_FORMAT);
            $endTime = date("H:i:s", strtotime($dt->format(self::HOUR_FORMAT) . " + " . self::TEN_MINUTES));

            $slot = DB::table('school_reservation_slots AS srs')
                ->select('srs.id')
                ->selectRaw('srs.slot_count - 
                            IF (ISNULL(SUM(rs.reserved_slot_count)), 0, SUM(rs.reserved_slot_count))  AS remaining_slot_count')
                ->leftJoin('school_reserved_slots AS rs', function($join){
                    $join->on('rs.school_reservation_slot_id', '=', 'srs.id');
                    $join->whereNull('rs.deleted_at');
                })
                ->where('srs.reservation_slot_date', $dt->format(self::DATE_FORMAT))
                ->where('srs.start_time', $startTime)
                ->where('srs.end_time', $endTime)
                ->groupBy('srs.id')
                ->havingRaw("remaining_slot_count >= $students")
                ->first();

            if (!$slot){  
                return false;
            }
        }

        return true;
    }
    
    public function updateLessonReservationTextbookLessonId($id, $textbookLessonId)
    {
        $lessonReservation = LessonReservation::find($id);

        if(!$lessonReservation) {
            return false;
        }

        if($lessonReservation->mst_textbook_lesson_id != $textbookLessonId) {
            $lessonReservation->mst_textbook_lesson_id = $textbookLessonId;
            $lessonReservation->updated_at = new DateTime();

            return $lessonReservation->save();
        }
        
        return true;
    }

    function schoolRequestLessonReservationForChecking($fromQuery)
    {
        $data =[];
        foreach ($fromQuery as $list){
            $generateDatetimeStarttimeEndtime = self::generateDatetimeStarttimeEndtime($list);
            $data []= self::schoolRequestLessonReservationArray($list, $generateDatetimeStarttimeEndtime['startTime'], $generateDatetimeStarttimeEndtime['endTime'], $generateDatetimeStarttimeEndtime['dateTimeNow']);
        }
        return $data;
    }

    public function checkExistMatchedReservation($classListId)
    {
        $lessonReservations = SchoolRequestLessonReservation::where('class_list_id', $classListId)->get();
        
        if ($lessonReservations->isNotEmpty()){
            $lessonReservations = self::schoolRequestLessonReservationForChecking($lessonReservations);
            $result=[];
            foreach($lessonReservations as $reservation){
               if ($reservation['status'] == self::AFTER_MATCHING_FINISHED_COMPLETED && date('Y-m-d H:i:s',strtotime($reservation['dateTimeNow'])) < date('Y-m-d H:i:s',strtotime($reservation['end_time']))){
                    return true;
                }
            }
        }
        return false;
    }

    public function cancelReservationForDeletedClass($classListId)
    {
        DB::beginTransaction();
        try {
            $reservations = DB::table('school_request_lesson_reservations')->where('class_list_id', $classListId)->get()->toArray();
            $delete = [];
            if (!empty($reservations)){
                foreach($reservations as $reservation){
                    DB::table('school_request_lesson_reservations')
                        ->join("lesson_reservations","school_request_lesson_reservations.id","=","lesson_reservations.school_request_lesson_reservation_id")
                        ->where('school_request_lesson_reservations.id', $reservation->id)
                        ->update(['lesson_reservations.status' => self::CANCELLED, 
                        'school_request_lesson_reservations.status' => self::CANCELLED]);
                    $delete = SchoolReservedSlot::where('school_request_lesson_reservation_id', $reservation->id)->delete();
                }
            }
            DB::commit();
            return $delete;
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollback();
            return response()->json([
                "status" => false
            ]);
        }
    }

    public function reservationListQuery($request)
    {
        $queryStr = 'fld-schoolYear='.$request->query('fld-schoolYear').'&';
        $queryStr .= 'fld-class='.$request->query('fld-class').'&';
        $queryStr .= 'fld-status='.$request->query('fld-status').'&';
        $queryStr .= 'fld-id='.$request->query('fld-id').'&';
        $queryStr .= 'fld-person='.$request->query('fld-person').'&';
        $queryStr .= 'daterange='.$request->query('daterange');

        return $queryStr;
    }

    public function getWithProblemReportReasonTypes($id, $studentId)
    {
        return DB::table('lesson_reservations AS lr')
            ->select('lr.id', 'lr.tutor_id', 'pr.reason_type', 'pr.details')
            ->leftJoin('problem_reports AS pr','pr.lesson_reservation_id', '=', 'lr.id')
            ->where('lr.id', $id)
            ->where('lr.student_id', $studentId)
            ->first();
    }
}
