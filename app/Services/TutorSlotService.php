<?php

namespace App\Services;

use App\Models\TutorSlot;

class TutorSlotService
{
    const STATUS_OPEN = 1;
    const STATUS_BOOKED = 2;
    const STATUS_ABSENT = 3;

    public function create($tutorId, $reservationSlotId)
    {
        $tutorSlot = new TutorSlot();
        $tutorSlot->tutor_id = $tutorId;
        $tutorSlot->reservation_slot_id = $reservationSlotId;
        $tutorSlot->status = self::STATUS_OPEN;

        if (!$tutorSlot->save()) {
            return false;
        }

        return $tutorSlot;
    }

    public function book($id)
    {
        $tutorSlot = TutorSlot::find($id);
        $tutorSlot->status = self::STATUS_BOOKED;

        if (!$tutorSlot->save()) {
            return false;
        }

        return $tutorSlot;
    }

    public function getEarliestTutorInSlot($reservationSlotId)
    {
        return TutorSlot::where('reservation_slot_id', $reservationSlotId)
            ->where('status', self::STATUS_OPEN)
            ->orderBy('created_at', 'ASC')
            ->first();
    }

    public function findByTutorIdReservationSlotId($tutorId, $reservationSlotId)
    {
        return TutorSlot::where('tutor_id', $tutorId)
            ->where('reservation_slot_id', $reservationSlotId)
            ->first();
    }
}
