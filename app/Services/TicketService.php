<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\LessonReservation;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use DB;
use Carbon\Carbon;

class TicketService
{

    public function showTickets($id)
    {
        $tickets = DB::table("student_tickets")
        ->join('lesson_reservations','student_tickets.lesson_reservation_id', '=', 'lesson_reservations.id')
        ->join('students','student_tickets.student_id', '=', 'students.id')
        ->join('class_lists','students.class_list_id', '=', 'class_lists.id')
        ->join('school_reservation_slots','lesson_reservations.school_reservation_slot_id', '=', 'school_reservation_slots.id')
        ->select('student_tickets.*','lesson_reservations.school_reservation_slot_id','lesson_reservations.status', 'school_reservation_slots.reservation_slot_date', 'school_reservation_slots.start_time', 'school_reservation_slots.end_time')
        ->where('student_tickets.student_id', '=',  $id)
        ->whereNull('lesson_reservations.class_list_id')
        ->where('lesson_reservations.status', '!=', config('constants.ON_GOING'))
        ->orderByDesc('school_reservation_slots.reservation_slot_date')
        ->get();

        return $tickets;
    }
    
    public function showGrantTickets($id)
    {
        $grantTickets = DB::table("student_tickets")
        ->select('lesson_reservation_id','expire_date')
        ->where('student_tickets.student_id', '=',  $id)
        ->whereNull('lesson_reservation_id')
        ->get();
        
        return $grantTickets;
    }

    public function grantTicket($id)
    {
        $expireDate = new Carbon('last day of next month');
        $ticket = new Ticket([
            "student_id" => $id,
            "expire_date" => $expireDate,
            "created_by" => config('constants.SYSTEM_USER'),
        ]);
        return $ticket->save();
    }

    public function store($ticket)
    {
        return Ticket::create($ticket);
    }

    public function isTicketExists($ticket)
    {
        return Ticket::where('student_id', $ticket['student_id'])
            ->where('lesson_reservation_id', $ticket['lesson_reservation_id'])       
            ->exists();
    }
}