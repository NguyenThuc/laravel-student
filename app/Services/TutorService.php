<?php

namespace App\Services;
use Carbon\Carbon;
use App\Services\RareJobEMSService;
use App\Services\LessonReservationService;
use App\Models\Tutor;

class TutorService
{
    public function view()
    {
        $tutorDetails = array( 
            'name' => 'マーガ',
            'gender' => '女性',
            'age' => '20',
            'kana_description' => 'ここ数年、私は専門家や大学生に英語でのスピーキング、ライティング、プレゼンテーションの準備を訓練してきました。 私はあなたがあなたの目標を達成するのを手伝いましょう。 学習体験を皆さんと共有できれば幸いです。',
            'english_description' => 'Over the last few years, I have trained professionals and college students in speaking, writing, and preparing for presentations in English. I will help you reach your goals. We hope you can share your learning experience with you.'  
          );
        return $tutorDetails;
    }


    public function findByRarejobId($rarejobId)
    {
        return Tutor::where('rarejobTutorId', $rarejobId)->first();
    }

    public function create($rarejobTutorId, $company, $operator)
    {
        $tutor = new Tutor();
        $tutor->rarejobTutorId = $rarejobTutorId;
        $tutor->company = $company;
        $tutor->operator = $operator;

        return $tutor->save();
    }

    public function age($birthday)
    {
        return $years = Carbon::parse($birthday)->age;
    }

    public function getSchedules($tutorId)
    {
        $schedules = [];
        $rareJobEMSService = new RareJobEMSService();
        $lessonReservationService = new LessonReservationService();
        
        $events = $rareJobEMSService->getTutorTodaysEvents($tutorId);

        foreach($events as $event){

            // check if the schedule is not cancelled
            if (isset($event->attendee_id)){
            
                // check if Rarejob lessons,
                // if true no need to check in lesson_reservations table
                if (in_array(config('constants.RAREJOB_PRODUCT_CODE'), $event->product_ids)){

                    $event->{'lesson_type'} = config('constants.RAREJOB_EKAIWA');

                    array_push($schedules, $event);

                } else {
                // EU Lessons
                    $lessonReservation = $lessonReservationService->searchByEMSEventId($event->id);

                    if ($lessonReservation){

                        $event->{'lesson_reservation_id'} = $lessonReservation->id;
                        $event->{'lesson_type'} = $lessonReservation->school_request_lesson_reservation_id ? config('constants.SCHOOL_LESSON') : config('constants.HOME_LESSON');

                        array_push($schedules, $event);
                    }
                }
            }
        }

        return $schedules;
    }
    
}