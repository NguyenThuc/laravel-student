<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class LessonConditionLogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table("lesson_condition_logs")->insert([
            [ "lesson_reservation_id" => 1, "actor_id" => 1, "actor_type" => 1, "action" => 1, "action_datetime" => date("Y-m-d H:i:s")],
            [ "lesson_reservation_id" => 2, "actor_id" => 1, "actor_type" => 1, "action" => 2, "action_datetime" => date("Y-m-d H:i:s")],
            [ "lesson_reservation_id" => 3, "actor_id" => 2, "actor_type" => 2, "action" => 1, "action_datetime" => date("Y-m-d H:i:s")],
        ]);
    }
}
