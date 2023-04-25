<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach(range(1, 15) as $item){
            DB::table('students')->insert([
                ['school_id' => $item, 'parent_id' => $item, 'rarejob_student_id' => '01-00-1' . (10 + $item), 'class_list_id' => $item, 'attendance_id' => $item, 'agree_terms' => 1, 'created_at' => now()],
            ]);
        }
    }
}
