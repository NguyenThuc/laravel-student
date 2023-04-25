<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class ClassAttendeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('class_attendees')->insert([
            ['class_list_id' => 1, 'student_id' => 1, 'created_at' => now()],
            ['class_list_id' => 2, 'student_id' => 2, 'created_at' => now()],
            ['class_list_id' => 3, 'student_id' => 3, 'created_at' => now()],
            ['class_list_id' => 4, 'student_id' => 4, 'created_at' => now()],
            ['class_list_id' => 5, 'student_id' => 5, 'created_at' => now()],
        ]);
    }
}
