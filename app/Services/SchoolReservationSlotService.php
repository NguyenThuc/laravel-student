<?php

namespace App\Services;

use App\Models\LessonReservation;
use App\Models\SchoolReservationSlot;
use App\Models\MstTextbookCategory;
use App\Models\MstTextbookLesson;
use App\Models\MstTextbookCourse;
use App\Models\SchoolRequestLessonReservation;
use App\Models\SchoolReservedSlot;

class SchoolReservationSlotService
{
    public function getAll()
    {
        return SchoolReservationSlot::all();
    }

    public function getAllWithRemainingSlotCount($dateStart, $dateEnd)
    {        
        return SchoolReservationSlot::select('school_reservation_slots.*')
            ->selectraw('school_reservation_slots.slot_count - 
                        IF (ISNULL(SUM(rs.reserved_slot_count)), 0, SUM(rs.reserved_slot_count))  AS remaining_slot_count,
                        GROUP_CONCAT(srlr.school_id) AS schools')
            ->leftJoin('school_reserved_slots AS rs', function($join){
                    $join->on('rs.school_reservation_slot_id', '=', 'school_reservation_slots.id');
                    $join->whereNull('rs.deleted_at');
                })
            ->leftJoin('school_request_lesson_reservations AS srlr', 'srlr.id', '=', 'rs.school_request_lesson_reservation_id')
            ->whereBetween('reservation_slot_date', [$dateStart, $dateEnd])
            ->groupBy('school_reservation_slots.id')
            ->get();
    }

    public function schoolSlotDetails($id)
    {
        $lessonDetails = LessonReservation::with(['masterTextbook'])->where("id", $id)->first();

        if (!$lessonDetails){
            return false;
        }

        $lessonRequest = SchoolRequestLessonReservation::where(
            "id",
            $lessonDetails->school_request_lesson_reservation_id
        )->first();

        $textbookLessonId = $lessonDetails->mst_textbook_lesson_id;
        if (!$textbookLessonId) {
            $textbookLessonId = $lessonRequest->mst_textbook_lesson_id;
        }

        if (!$textbookLessonId) {
            return [
                "lessonDetails" => $lessonDetails,
                "category" => "",
                "unit" => "",
                "request" => $lessonRequest
            ];
        }

        $lessonDtls = MstTextbookLesson::where("id", $textbookLessonId)->first();
        $courseDtls = MstTextbookCourse::where("id", $lessonDtls->mst_textbook_course_id)->first();
        $categoryDtls = MstTextbookCategory::where("id", $courseDtls->mst_textbook_category_id)->first();

        return [
            "lessonDetails" => $lessonDetails,
            "category" => $categoryDtls->name ?? $categoryDtls->name_en,
            "unit" => $lessonDtls->name ?? $lessonDtls->name_en,
            "request" => $lessonRequest
        ];
    }

    public function getSlotsByTutor($tutorId)
    {     
        return SchoolReservationSlot::select('ls.id', 't.first_name', 'start_time', 'end_time')
            ->join('lesson_reservations AS ls', 'ls.school_reservation_slot_id', '=', 'school_reservation_slots.id')
            ->join('teachers AS t', 't.id', '=', 'ls.tutor_id')
            ->where('tutor_id', $tutorId)
            ->get();
    }

    public function getOpenSlots(){
        return SchoolReservationSlot::where('reservation_slot_date', date('Y-m-d'))
            ->get();
    }
}