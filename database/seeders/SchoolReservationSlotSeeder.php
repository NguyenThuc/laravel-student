<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Datetime;
use DateInterval;
use DatePeriod;
use DB;

class SchoolReservationSlotSeeder extends Seeder
{
    const DATETIME_FORMAT = 'Y-m-d H:i:s';
    const DATE_FORMAT = 'Y-m-d';
    const TIME_FORMAT = 'H:i:s';
    const DATE_START = '2022-03-01';
    const DATE_END = '2023-03-31';
    const TIME_START = '08:30:00';
    const TIME_END = '17:50:00';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dateStart = new DateTime(self::DATE_START);
        $dateEnd = new DateTime(self::DATE_END);

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($dateStart, $interval, $dateEnd);

        foreach ($period as $dt) {
            $date = $dt->format(self::DATE_FORMAT);

            if (!$this->isWeekend($date)){
                $timeStart = new DateTime($date . " " . self::TIME_START);
                $timeEnd =  new DateTime($date . " " . self::TIME_END);

                $interval = DateInterval::createFromDateString('10 minutes');
                $period = new DatePeriod($timeStart, $interval, $timeEnd);
                
                foreach ($period as $dt) {
                    $timeStart = $dt->format(self::TIME_FORMAT);
                    $timeEnd =  date(self::TIME_FORMAT, strtotime($dt->format(self::TIME_FORMAT) . ' + 10 minutes'));

                    DB::table('school_reservation_slots')->insert(
                        ['reservation_slot_date' => $dt->format(self::DATE_FORMAT), 'start_time' => $timeStart, 'end_time' => $timeEnd, 'slot_count' => 10, 'created_by' => 'Admin', 'created_at' => now()],
                    );
                }
            }
        }
    }

    protected function isWeekend($date) {
        return (date('N', strtotime($date)) >= 6);
    }
}
