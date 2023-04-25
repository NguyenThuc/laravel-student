<?php

namespace App\Services;

use App\Models\LessonReservation;
use App\Models\TutorLessonRating;
use DB;

class TutorLessonRatingService extends RareJobStudentService
{
    const STATUS_CONFIRMED = '1';
    const STATUS_ONGOING = '2';
    const STATUS_CANCELLED = '3';
    const STATUS_PENDING = '4';
    const STATUS_TEXT_CONFIRMED = 'Confirmed';
    const STATUS_TEXT_ONGOING = 'Ongoing';
    const STATUS_TEXT_CANCELLED = 'Cancelled';
    const STATUS_TEXT_PENDING = 'Pending';

    public function tutorLessons($tutorId)
    {
        $lessonSched = [];
        $lessons = LessonReservation::where("tutor_id", $tutorId)->with(['schoolReservationSlot', 'student'])->get();

        foreach($lessons as $lesson) {
            $dateFormat = date(config('constants.MonthInWord'), strtotime($lesson->lesson_date));
            $dateFormat .= date(config('constants.TimeIndicator'), strtotime($lesson->schoolReservationSlot->start_time)).' - ';
            $dateFormat .= date(config('constants.TimeIndicator'), strtotime($lesson->schoolReservationSlot->end_time));
            $student = $this->find($lesson->student->rarejob_student_id);

            $lessonSched[] = [
                "id" => $lesson->id,
                "date" => $dateFormat,
                "name" => $student->name
            ];
        }

        return $lessonSched;
    }

    public function tutorNotes($lessonId)
    {
        $details = LessonReservation::where('id', $lessonId)->with(['schoolReservationSlot', 'student', 'masterTextbook', 'classList'])->firstOrFail();
        $student = $this->find($details->student->rarejob_student_id);
        if($details->status == self::STATUS_CONFIRMED) {
            $type = self::STATUS_TEXT_CONFIRMED;
        } elseif($details->status == self::STATUS_ONGOING) {
            $type = self::STATUS_TEXT_ONGOING;
        } elseif($details->status == self::STATUS_CANCELLED) {
            $type = self::STATUS_TEXT_CANCELLED;
        } elseif($details->status == self::STATUS_PENDING) {
            $type = self::STATUS_TEXT_PENDING;
        }
        return [
            "id" => $details->id,
            "tutor" => $details->tutor_id,
            "student" => $details->student_id,
            "date" => date(config('constants.MonthInWord'), strtotime($details->lesson_date)).' - '.date('l', strtotime($details->lesson_date)),
            "time" => date(config('constants.TimeIndicator'), strtotime($details->schoolReservationSlot->start_time)).' - '.date(config('constants.TimeIndicator'), strtotime($details->schoolReservationSlot->end_time)),
            "type" => $type,
            "name" => $student->name,
            "gender" => "",
            "grade" => $details->classList->year->name,
            "material" => $details->masterTextbook->name,
            "meeting_id" => $details->meeting_id,
            "meeting_password" => $details->meeting_password,
            "meeting_hostkey" => $details->meeting_hostkey
        ];
    }

    public function tutorFeed($data)
    {
        $feedback = new TutorLessonRating([
            'lesson_reservation_id' => $data['lesson'],
            'tutor_id' => $data['tutor'], 
            'student_id' => $data['student'], 
            'student_attendance' => $data['attendance'], 
            'assessment_score' => $data['score'], 
            'comment' => $data['comment'], 
            'created_by' => $data['createdBy']
        ]);

        return $feedback->save();
    }

    public function getPendingTutorLessonReservation($tutorId)
    {
        $lessonReservationIds = array();

        $result = DB::table("tutor_lesson_ratings")->
            select("lesson_reservation_id")->
            groupBy("lesson_reservation_id")->get();

        foreach($result as $key) {
            array_push($lessonReservationIds, $key->lesson_reservation_id);
        }            

        return LessonReservation::where("tutor_id", $tutorId)
            ->whereNotIn("id", $lessonReservationIds)
            ->get();
    }
    
    public function getByLessonReservationId($lessonReservationId)
    {
        return TutorLessonRating::where("lesson_reservation_id", $lessonReservationId)->first();
    }
}
