<?php

namespace App\Services;

use App\Models\LessonConditionLog;
use App\Models\LessonReservation;
use App\Models\SchoolRequestLessonReservation;

class LessonMonitoringService {

    const ACTION_TYPE_JOIN = 1;
    const ACTION_TYPE_UNJOIN = 2;
    const ACTION_TYPE_ENDED = 3;
    const ACTOR_TYPE_STUDENT = 1;
    const ACTOR_TYPE_TUTOR = 2;
    const ACTION_JOIN = 'join';
    const ACTION_UNJOIN = 'unjoin';
    const ACTION_ENDED = 'ended';
    const ACTOR_STUDENT = 'student';
    const ACTOR_TUTOR = 'teacher';
    const ACTION_DATETIME_DEFAULT = '未入室';

    public function getAll()
    {
        return LessonConditionLog::all();
    }

    public function findById($id)
    {
        return LessonConditionLog::find($id);
    }   

    public function findByLessonReservationId($id)
    {
        return LessonConditionLog::where("lesson_reservation_id", $id);
    }

    public function create($lessonReservationId, $actor_id, $action, $actor_type,  $action_datetime, $meeting_id)
    {
        $actionTypeArr = [
            self::ACTION_JOIN => self::ACTION_TYPE_JOIN,
            self::ACTION_UNJOIN => self::ACTION_TYPE_UNJOIN,
            self::ACTION_ENDED => self::ACTION_TYPE_ENDED
        ];

        switch ($action) {
            case self::ACTION_JOIN:
                $action = self::ACTION_TYPE_JOIN;
                break;
            case self::ACTION_UNJOIN:
                $action = self::ACTION_TYPE_UNJOIN;
                break;
            case self::ACTION_ENDED:
                $action = self::ACTION_TYPE_ENDED;
                break;
            default:
                throw new \Exception('invalid action type', ['action_type' => $action]);
        }

        switch ($actor_type) {
            case self::ACTOR_STUDENT:
                $actor_type = self::ACTOR_TYPE_STUDENT;
                break;
            case self::ACTOR_TUTOR:
                $actor_type = self::ACTOR_TYPE_TUTOR;
                break;
            default:
                $actor_type = 0;
        }

        $logSave = new LessonConditionLog([
            "lesson_reservation_id" => $lessonReservationId,
            "actor_id" => $actor_id,
            "actor_type" => $actor_type,
            "action" => $action,
            "action_datetime" => $action_datetime,
            "meeting_id" => $meeting_id
        ]);

        if (!$logSave->save()) {
            return false;
        }

        return $logSave;
    }

    public function getAllBySchoolRequestLessonReservationId($srlrId)
    {
        $studentRarejobService = new RareJobStudentService();
        $lessonReservations = LessonReservation::where('school_request_lesson_reservation_id', $srlrId)->get();

        if ($lessonReservations->isEmpty()) {
            return [];
        }

        $srlr = SchoolRequestLessonReservation::where('id', $srlrId)->first();
        $defaultTextbook = $srlr->mstTextbookLesson;
        $defaultTextbookName = $defaultTextbook->name ?? $defaultTextbook->name_en;

        $lessonReservationIds = [];
        foreach ($lessonReservations as $lessonReservation) {
            $lessonReservationIds[] = $lessonReservation->id;
        }

        $lessonConditionLogs = LessonConditionLog::whereIn(
            'lesson_reservation_id',
            $lessonReservationIds
        )->orderBy('action_datetime', 'asc')
        ->get();

        $logs = [];
        foreach ($lessonReservations as $lessonReservation) {
            $student = $lessonReservation->student;
            if (!$student) {
                continue;
            }

            $studentRarejobInfo = $studentRarejobService->getStudentProfile($student->rarejob_student_id);
            $name = "";
            if ($studentRarejobInfo->student_profile->profile) {
                $name = $studentRarejobInfo->student_profile->profile->lastName . ' ' . $studentRarejobInfo->student_profile->profile->firstName;
            }

            $logs[$lessonReservation->id]['name'] = $name;
            $logs[$lessonReservation->id]['attendance_id'] = $student->attendance_id;
            $lessonReservation->masterTextbook;

            $logs[$lessonReservation->id]['material_name'] = $defaultTextbookName;
            if ($lessonReservation->masterTextbook) {
                $logs[$lessonReservation->id]['material_name'] = $lessonReservation->masterTextbook->name ?? $lessonReservation->masterTextbook->name_en;
            }

            $logs[$lessonReservation->id]['tutor_join_datetime'] = self::ACTION_DATETIME_DEFAULT;
            $logs[$lessonReservation->id]['student_join_datetime'] = self::ACTION_DATETIME_DEFAULT;

            foreach ($lessonConditionLogs as $key => $lessonConditionLog) {
                if ($lessonConditionLog->lesson_reservation_id == $lessonReservation->id) {
                    if ($lessonConditionLog->action == self::ACTION_TYPE_JOIN) {
                        if ($lessonConditionLog->actor_type == self::ACTOR_TYPE_TUTOR) {
                            $logs[$lessonReservation->id]['tutor_join_datetime'] = $lessonConditionLog->action_datetime;
                        }

                        if ($lessonConditionLog->actor_type == self::ACTOR_TYPE_STUDENT) {
                            $logs[$lessonReservation->id]['student_join_datetime'] = $lessonConditionLog->action_datetime;
                        }
                    }
                }
            }
        }

        return $logs;
    }
}