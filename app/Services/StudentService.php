<?php

namespace App\Services;

use Carbon\Carbon;
use PDF;

use App\Models\Student;
use App\Models\StudentLessonNote;
use App\Models\ClassList;
use App\Models\ClassAttendee;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\Models\LessonReservation;
use App\Models\SchoolReservedSlot;
use App\Models\SchoolRequestLessonReservation;
use App\Models\MstTextbookLesson;
use App\Services\LessonReservationService;

class StudentService extends RareJobStudentService
{   
    use SoftDeletes;

    const STUDENT_DETAILS_TITLE = '学生の詳細';
    const CLASS_LIST_TITLE = 'クラスリスト';
    const APP_LOGIN_PATH = '/login';
    const ONGOING = 1;
    const CANCELLED_BOOKING = 3;
    const MATCHED_BOOKING = 2;
    const DELETE_FALSE = 0;
    const DELETE_FALSE_RESERVATION_EXIST = 1;

    private $validIncludeProps = [
        "school"
    ];

    public function view($id)
    {
        return Student::where('id', $id)->first();
    }

    public function registrationForm()
    {
        $data = [
            'title' => 'Escholar Student Registration Form',
            'date' => date('m/d/Y')
        ];
          
        $pdf = PDF::loadView('pdf.registration', $data);
    
        return $pdf->download('registration-form.pdf');
    }

    public function store($data)
    {
        $generateCount = $data['addtl'];
        $generatedInfos = [];
        $generatedIds = [];
        for($count = 1; $count <= $generateCount; $count++) {
            $name = Str::random(10);
            $email = Str::random(10).'@edule.jp';
            $password = Str::random(8);

            $provisionalData = array(
                        'name' => $name, 
                        'password' => $password,
                        'email' => $email
                    );
    
            $provisionalToken = $this->provisional_registration($provisionalData);

            $tokenVerified = $this->provisional_registration_verify($provisionalToken->provisional_token);
            
            $generatedInfos[] = [
                "id" => $tokenVerified->student,
                "email" => $email
            ];
        }

        DB::beginTransaction();
        try {
            $attendanceId = $this->getLastAttendanceIdByClassId($data['classListId']);
            foreach($generatedInfos as $info) {


                $student =$this->createWithClassAttendee($data['schooId'], $data['classListId'], $info['id'], $attendanceId, $info['email'], $password);

                $generatedIds[] = $student->id;
                $attendanceId++;
            }

            DB::commit();
            return response()->json([
                "status" => true,
                "data" => $generatedIds
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollback();
            return response()->json([
                "status" => false,
                "data" => []
            ]);
        }
    }

    public function updated($data)
    {
        $student = Student::where('id', $data['id'])
            ->update([
                "school_id" => $data['schoolId'],
                "rarejob_student_id" => $data['studId'],
                "class_list_id" => $data['classList'],
                "attendance_id" => $data['attenId'],
                "updated_by" => $data['updateBy']
            ]);

        if (!$student) {
            return false;
        }
        
        return true;
    }

    function schoolRequestLessonReservationArray($list, $startTime, $endTime, $dateTimeNow)
    {
        $data = [
            "start_time" => $startTime,
            "end_time" => $endTime,
            "dateTimeNow" => $dateTimeNow,
            "status" => $list->status
        ]; 
        return $data;
    }

    function generateDatetimeStarttimeEndtime($list)
    {
        $duration = $list->duration * 60;
        $dateTimeNow = date(LessonReservationService::DATE_TIME_24HOUR_FORMAT);
        $startTime = $list->start_time;
        $startTime = strtotime($startTime);
        $startTime = date(LessonReservationService::DATE_TIME_24HOUR_FORMAT, $startTime);
        $endTime = strtotime($startTime) + $duration;
        $endTime = date(LessonReservationService::DATE_TIME_24HOUR_FORMAT, $endTime);  
           
        return ["dateTimeNow" => $dateTimeNow, "startTime" => $startTime, "endTime" => $endTime];
    }

    function canDelete($id)
    {
        $checkReservation = LessonReservation::where('student_id', $id)
            ->join(
                'school_request_lesson_reservations',
                'lesson_reservations.school_request_lesson_reservation_id',
                '=',
                'school_request_lesson_reservations.id'
                )
            ->whereIn(
                'lesson_reservations.status',
                [
                    LessonReservationService::BEFORE_MATCHING,
                    LessonReservationService::AFTER_MATCHING_FINISHED_COMPLETED
                ]
            )
            ->get();
        if ($checkReservation->isNotEmpty()) {
            $lessonReservations = [];
            foreach ($checkReservation as $list) {
                $generateDatetimeStarttimeEndtime = self::generateDatetimeStarttimeEndtime($list);
                $lessonReservations[] = self::schoolRequestLessonReservationArray(
                    $list,
                    $generateDatetimeStarttimeEndtime['startTime'],
                    $generateDatetimeStarttimeEndtime['endTime'],
                    $generateDatetimeStarttimeEndtime['dateTimeNow']);
            }

            foreach ($lessonReservations as $reservation) {
                if ($reservation['status'] == LessonReservationService::AFTER_MATCHING_FINISHED_COMPLETED &&
                    date('Y-m-d H:i:s',strtotime($reservation['dateTimeNow'])) < date('Y-m-d H:i:s',strtotime($reservation['end_time']))
                   ){
                    return false;
                }
            }
        }
        return $checkReservation;
    }
    
    public function delete($id)
    {  
        $deleteData = $this->canDelete($id);

        if (!$deleteData) {
            return self::DELETE_FALSE_RESERVATION_EXIST;
        }
    
        DB::beginTransaction();
        try {
            $student = $this->getById($id);
            $this->unsubscribe($student->rarejob_student_id);
            ClassAttendee::where('student_id', $id)->delete();
            LessonReservation::where('student_id',$id)
                ->where('status',LessonReservationService::BEFORE_MATCHING)
                ->update([
                    'status' => LessonReservationService::CANCELLED, 
                    'deleted_at' => date(LessonReservationService::MYSQL_DATE_TIME_FORMAT)
                ]);
            foreach ($deleteData as $reservation) {
                if ($reservation->status == LessonReservationService::BEFORE_MATCHING){
                    $schoolReservedSlot = SchoolReservedSlot::where(
                        'school_request_lesson_reservation_id',
                        $reservation->school_request_lesson_reservation_id
                        )->get();
                    foreach ($schoolReservedSlot as $reservedSlot){
                        $reservedSlot->reserved_slot_count = $reservedSlot->reserved_slot_count - 1;
                        $reservedSlot->save();
                    }
                        
                    if ($reservedSlot->reserved_slot_count < 1){
                        SchoolRequestLessonReservation::where('id', $reservation->school_request_lesson_reservation_id)
                            ->update([
                                'status' => LessonReservationService::CANCELLED,
                                'deleted_at' => date(LessonReservationService::MYSQL_DATE_TIME_FORMAT)
                            ]);
                        SchoolReservedSlot::where('school_request_lesson_reservation_id',$reservation->school_request_lesson_reservation_id)->delete();
                    }
                }
            }   
            $student->delete($id);
            DB::commit();
            return $student;
                DB::commit();
                return $student;
            } catch (\Exception $e) {
                Log::error($e);
                DB::rollback();
                return self::DELETE_FALSE;
            }
    }

    public function registeredClass($schoolId)
    {
        return ClassList::with(['year', 'class'])
            ->where('school_id', $schoolId)
            ->get();
    }

    public function registerCondition($schoolId, $classId)
    {
        return Student::where("school_id", $schoolId)->where("class_list_id", $classId)->get();
    }
    
    public function createWithClassAttendee($schoolId, $classList, $rarejobStudent, $attendanceId, $email, $password)
    {
        $password = Crypt::encryptString($password);

        $student = new Student([
            "school_id" => $schoolId,
            "class_list_id" => $classList,
            "rarejob_student_id" => $rarejobStudent,
            "attendance_id" => $attendanceId,
            "rarejob_email" => $email,
            "password" => $password
        ]);

        $student->save();

        $attendee = new ClassAttendee([
            "class_list_id" => $classList,
            "student_id" => $student->id
        ]);
        $attendee->save(); 
        
        return $student;
    }
    
    private function getLoginUrl($schoolId, $studentId)
    {
        $params = [
            "school_id" => $schoolId,
            "student_id" => $studentId
        ];
        $queryString = http_build_query($params);

        $url = env('STUDENT_APP_URL') . self::APP_LOGIN_PATH . "?" . $queryString;

        return $url;
    }
    
    public function generatePdfDetails($id)
    {
        $students = $this->getStudentList($id, false);

        $data = [
            'title' => self::STUDENT_DETAILS_TITLE,
            'students' => $students
        ];
          
        $pdf = PDF::loadView('pdf.student-details', $data);
    
        return $pdf->stream('student-details.pdf');
    }

    public function generatePdfByClassListId($id)
    {
        $students = $this->getStudentList($id);

        $data = [
            'title' => self::CLASS_LIST_TITLE,
            'students' => $students
        ];
          
        $pdf = PDF::loadView('pdf.class-list', $data);
    
        return $pdf->stream('class-list.pdf');
    }

    private function getStudentList($id, $isClassList = true){

        $classList = $this->joinStudentDetails();

        if ($isClassList){
            $classList->where('students.class_list_id', $id);
        } else {
            $classList->where('students.id', $id);
        }   

        $classList  =  $classList->get();

        $students = [];
        foreach ($classList as $cl){
       
            $url = $this->getLoginUrl($cl->school_id, $cl->id);

            $cl->qr_code = base64_encode(\QrCode::format('svg')->size(1)->errorCorrection('H')->generate($url));

            // get rarejob student info
            $rarejobInfo = $this->getStudentProfile($cl->rarejob_student_id, parent::PRODUCT_ID);   

            if (isset($rarejobInfo->student_profile)){
                $cl->firstname = $rarejobInfo->student_profile->profile->firstName ?? '';

                $cl->lastname = $rarejobInfo->student_profile->profile->lastName ?? '';
            }

            if ($cl->password){
                $cl->password = Crypt::decryptString($cl->password);
            }

            array_push($students, $cl);
        }

        return $students;
    }

    private function joinStudentDetails()
    {
        return Student::select('students.*', 
                's.id AS school_id', 
                's.name AS school', 
                'c.name AS course', 
                'mc.name AS class', 
                'y.name AS school_year')
        ->leftJoin('schools AS s', 's.id', '=', 'students.school_id')
        ->leftJoin('class_attendees AS ca', 'ca.student_id', '=', 'students.id')
        ->leftJoin('class_lists AS cl', 'cl.id', '=', 'ca.class_list_id')
        ->leftJoin('mst_courses AS c', 'c.id', '=', 'cl.mst_course_id')
        ->leftJoin('mst_years AS y', 'y.id', '=', 'cl.mst_year_id')
        ->leftJoin('mst_classes AS mc', 'mc.id', '=', 'cl.mst_class_id');
    }

    public function getStudentDetails($studentId)
    {
        $studentDetails = $this->joinStudentDetails()
            ->where('students.id', $studentId)
            ->first();

        if ($studentDetails){
            $rarejobInfo = $this->getStudentProfile($studentDetails->rarejob_student_id, parent::PRODUCT_ID); 

            if (isset($rarejobInfo->student_profile)){
                $studentDetails->firstName = $rarejobInfo->student_profile->profile->firstName ?? '';

                $studentDetails->lastName = $rarejobInfo->student_profile->profile->lastName ?? '';
            }
        }

        return $studentDetails;
    }

    public function getStudentLessonNote($studentId, $lessonReservationId)
    {
        return StudentLessonNote::where("student_id", $studentId)
            ->where("lesson_reservation_id", $lessonReservationId)
            ->first();
    }

    public function setStudentLessonNote($studentId, $lessonReservationId, $note)
    {
        $studentLessonNote = $this->getStudentLessonNote($studentId, $lessonReservationId);
        if ($studentLessonNote) {
            if($studentLessonNote['note'] != $note) {
                $studentLessonNote['note'] = $note;
                $studentLessonNote['updated_at'] =  Carbon::now()->format('Y-m-d H:i:s');
                $studentLessonNote->save();
            }
            return json_decode($studentLessonNote);
        }

        $newStudentLessonNote = new StudentLessonNote();
        $newStudentLessonNote->student_id = $studentId;
        $newStudentLessonNote->lesson_reservation_id = $lessonReservationId;
        $newStudentLessonNote->note = $note;
        $newStudentLessonNote->save();
        return json_decode($newStudentLessonNote);
    }

    public function getByParentId($parentId)
    {
        return Student::where("parent_id", $parentId)->first();
    }

    public function setParentId($id, $parentId)
    {
        $student = $this->getById($id);

        if($student->parent_id == null)
        {
            $student->parent_id = $parentId;
            $student->save();
        }

        return $student;
    }

    public function getStudentByRarejobStudentId($rarejobStudentId, $schoolId = null)
    {
        $student = Student::where("rarejob_student_id", $rarejobStudentId);

        if($schoolId) {
            $student->where("school_id", $schoolId);
        }

        return $student->first();
    }

    public function getInfo($studentId)
    {
        return Student::select('students.*', 'my.name AS grade', 'mc.name AS class')
            ->leftJoin('class_lists AS cl', 'cl.id', '=', 'students.class_list_id')
            ->leftJoin('mst_classes AS mc', 'cl.mst_class_id', '=', 'mc.id')
            ->leftJoin('mst_years AS my', 'cl.mst_year_id', '=', 'my.id')
            ->where('students.id', $studentId)->first();
    }

    public function getAll()
    {
        return Student::all();
    }

    public function getById($id, $request = null, $include = null)
    {
        $student = Student::find($id);
        if (!$student) {
            return [];
        }

        $includeProperty = [];

        if($student && ($request && $request->include) ||  $include) {
            $include = explode(",", $include ? $include : $request->include);

            foreach($this->validIncludeProps as $props) {
                if(in_array($props, $include)) {
                    array_push($includeProperty, $props);
                }
            }
        }

        return $student->load($includeProperty);
    }

    public function getLastAttendanceIdByClassId($classId)
    {
        $lastStudentAttendance = Student::where('class_list_id', $classId)
            ->orderBy('attendance_id', 'desc')
            ->first();

        if (!$lastStudentAttendance) {
            return 1;
        }

        return $lastStudentAttendance->attendance_id + 1;
    }

    public function getByClasslistId($classId)
    {
        return Student::where('class_list_id', $classId)
            ->get();
    }

    public function deleteByRareJobStudentId($rarejobStudentId)
    {
        return Student::where('rarejob_student_id', $rarejobStudentId)->delete();
    }

    public function getNextLesson($student) 
    {
        $srlr = SchoolRequestLessonReservation::where('school_id', $student->school_id)
            ->join(
                'lesson_reservations',
                'school_request_lesson_reservations.id',
                '=',
                'lesson_reservations.school_request_lesson_reservation_id'
            )
            ->select(
                "school_request_lesson_reservations.id",
                "school_request_lesson_reservations.mst_textbook_lesson_id as school_textbook_id",
                "school_request_lesson_reservations.start_time",
                "school_request_lesson_reservations.duration",
                "lesson_reservations.mst_textbook_lesson_id as lesson_textbook_id",
                "lesson_reservations.id as lesson_reservation_id"
            )
            ->where('lesson_reservations.student_id', $student->id)
            ->where('start_time', '>', date('Y-m-d H:i:s'))
            ->where('lesson_reservations.status', '!=', self::CANCELLED_BOOKING)
            ->whereNull('lesson_reservations.deleted_at')
            ->orderBy('start_time', 'asc')
            ->first();

        if (!$srlr) {
            return [];
        }

        $lessonDate = $srlr->getSchedule();



        $textbookData = MstTextbookLesson::where('id', $srlr->lesson_textbook_id)->first();
        if (!$textbookData) {
            $textbookData = MstTextbookLesson::where('id', $srlr->school_textbook_id)->first();
        }

        $textbookName = "";
        if ($textbookData) {
            $textbookName = $textbookData->name ?? $textbookData->name_en;
        }

        $materialCourse = $textbookData->mstTextbookCourse ?? null;
        $textBookCourseName = $materialCourse->name ?? $materialCourse->name_en ?? null;

        $materialCategory = $materialCourse->mstTextbookCategory ?? null;
        $textbookCategoryName = $materialCategory->name ?? $materialCategory->name_en ?? null;


        return [
            "lesson_material" => $textbookName,
            "lesson_course_name" => $textBookCourseName,
            "lesson_category_name" => $textbookCategoryName,
            "lesson_date" => $lessonDate,
            "school_request" => $srlr->id,
            "lesson_reservation_id" => $srlr->lesson_reservation_id
        ];
    }

    public function studentClass($classId)
    {
        return ClassList::where('id', $classId)
                ->with(['masterYear', 'masterClass'])
                ->first();
    }

    public function resetPass($id)
    {
        $student = Student::find($id);
        if (!$student) {
            return null;
        }

        // generate reset password token
        $resetPassTokenResponse = $this->resetLink($student->rarejob_email);
        if (!isset($resetPassTokenResponse->token)) {
            return null;
        }

        // reset password with token and random generated password
        $randPassword = Str::random(8);
        $this->resetPassword($randPassword, $resetPassTokenResponse->token);

        // update in DB
        $encryptedPass = Crypt::encryptString($randPassword);
        $student->password = $encryptedPass;
        $student->save();

        return $randPassword;
    }

    public function getAllLessonHistory($studentId, $isGreaterThanToday = false, $isDelay = true) {
        $startTime = $isDelay ? date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s"). ' -30 minutes')) : date("Y-m-d H:i:s");
        $status = array(self::MATCHED_BOOKING, self::CANCELLED_BOOKING);

        $lessonReservation = LessonReservation::select('lesson_reservations.*')
            ->selectRaw('IFNULL(tlr.assessment_score, "-") AS score')
            ->leftJoin('tutor_lesson_ratings AS tlr', 'tlr.lesson_reservation_id', '=', 'lesson_reservations.id')    
            ->where("lesson_reservations.student_id", $studentId)
            ->with([ "schoolRequestLessonReservation", "classList", "classList.masterClass", "teacher" ])
            ->whereNull('lesson_reservations.deleted_at');

        if($isGreaterThanToday) {
            $lessonReservation->whereRelation("schoolRequestLessonReservation", "start_time", ">=", $startTime);
            $lessonReservation->whereIn("status", $status);
            
            return  $lessonReservation->get()->sortBy('schoolRequestLessonReservation.start_time')->toArray();
        }
        
        $status = array(self::MATCHED_BOOKING);
        $lessonReservation->whereRelation("schoolRequestLessonReservation", "start_time", "<", $startTime);
        $lessonReservation->whereIn("status", $status);

        return  $lessonReservation->get()->sortByDesc('schoolRequestLessonReservation.start_time')->toArray();

    }

    public function generateBellbirdMeetingURL($meeting_id, $meeting_password)
    {
        return $meetingURL = env('BELLBIRD_MEETING_URL').$meeting_id."?joinPassword=".$meeting_password;
    }
}