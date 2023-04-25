<?php

namespace Database\Seeders;
use DB;
use Illuminate\Database\Seeder;

class MstTextBookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('mst_textbook_lessons')->insert([
            [
                'name' => 'Lesson1:Be Verb', 
                "mst_textbook_course_id" => 1, 
                "bellbird_lesson_id" => "d99b39e0-8566-11ec-9fa6-8706afbbc995",
                'created_at' => now()
            ],
            [
                'name' => 'Lesson2:Demonstrative Pronouns', 
                "mst_textbook_course_id" => 1, 
                "bellbird_lesson_id" => "ac59bc2a-8568-11ec-afe6-0fefa9004cb8",
                'created_at' => now()
            ],
            [
                'name' => 'Lesson1：挨拶', 
                "mst_textbook_course_id" => 2, 
                "bellbird_lesson_id" => "972b8efa-9378-11ec-946b-971cda88c12c",
                'created_at' => now()
            ],
            [
                'name' => 'Lesson2：自己紹介１', 
                "mst_textbook_course_id" => 2, 
                "bellbird_lesson_id" => "57f376b2-92f6-11ec-a0ad-4f2d8ff4646d",
                'created_at' => now()
            ]
        ]);
    }
}
