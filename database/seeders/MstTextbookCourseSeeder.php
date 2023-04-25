<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MstTextbookCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('mst_textbook_courses')->insert([
            [
                'name' => 'Compass Book1', 
                "mst_textbook_category_id" => 1, 
                "bellbird_course_id" => "74954054-8566-11ec-bf42-ebd9848dc8fd",
                'created_at' => now()
            ],
            [
                'name' => 'スターターレッスン Chapter 1', 
                "mst_textbook_category_id" => 2, 
                "bellbird_course_id" => "d00ef4d2-92f6-11ec-bdc9-e3bdd5f0c177",
                'created_at' => now()
            ]
        ]);
    }
}
