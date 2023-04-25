<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("schools")->insert([
            // SL
            [ "name" => 'SL School 1', "contract_type" => "SL", "lesson_duration" => 3, "show_paid_guidance" => 0, "representative_teacher_id" => 1, "phone_number" => "08836433643", "mst_textbook_category_ids" => "1,2,3,4,5,6,7,8,9", "created_at" => now()],
            [ "name" => 'SL School 2', "contract_type" => "SL", "lesson_duration" => 1, "show_paid_guidance" => 0, "representative_teacher_id" => 2, "phone_number" => "08836433643", "mst_textbook_category_ids" => "1,2,3,4,5,6,7,8,9", "created_at" => now()],
            [ "name" => 'SL School 3', "contract_type" => "SL", "lesson_duration" => 2, "show_paid_guidance" => 0, "representative_teacher_id" => 3, "phone_number" => "08836433643", "mst_textbook_category_ids" => "1,2,3", "created_at" => now()],
            [ "name" => 'SL School 4', "contract_type" => "SL", "lesson_duration" => 1, "show_paid_guidance" => 0, "representative_teacher_id" => 4, "phone_number" => "08836433643", "mst_textbook_category_ids" => "1", "created_at" => now()],
            [ "name" => 'SL School 5', "contract_type" => "SL", "lesson_duration" => 2, "show_paid_guidance" => 0, "representative_teacher_id" => 5, "phone_number" => "08836433643", "mst_textbook_category_ids" => "1,2,3,4,5,6,7", "created_at" => now()],

            // HL
            [ "name" => 'HL School 6', "contract_type" => "HL", "lesson_duration" => '', "show_paid_guidance" => 0, "representative_teacher_id" => 6, "phone_number" => "08836433643", "mst_textbook_category_ids" => "1,2,3,4,5,6,7", "created_at" => now()],
            [ "name" => 'HL School 7', "contract_type" => "HL", "lesson_duration" => '', "show_paid_guidance" => 0, "representative_teacher_id" => 7, "phone_number" => "08836433643", "mst_textbook_category_ids" => "1,2,3,4,5,6,7", "created_at" => now()],
            [ "name" => 'HL School 8', "contract_type" => "HL", "lesson_duration" => '', "show_paid_guidance" => 0, "representative_teacher_id" => 8, "phone_number" => "08836433643", "mst_textbook_category_ids" => 1, "created_at" => now()],
            [ "name" => 'HL School 9', "contract_type" => "HL", "lesson_duration" => '', "show_paid_guidance" => 0, "representative_teacher_id" => 9, "phone_number" => "08836433643", "mst_textbook_category_ids" => "1,2,3", "created_at" => now()],
            [ "name" => 'HL School 10', "contract_type" => "HL", "lesson_duration" => '', "show_paid_guidance" => 0, "representative_teacher_id" => 10, "phone_number" => "08836433643", "mst_textbook_category_ids" => "2,4,6", "created_at" => now()],

            // SL/HL
            [ "name" => 'HL/SL School 11', "contract_type" => "HL/SL", "lesson_duration" => 1, "show_paid_guidance" => 1, "representative_teacher_id" => 11, "phone_number" => "08836433643", "mst_textbook_category_ids" => "1,2,3,4,5,6,7,8,9", "created_at" => now()],
            [ "name" => 'HL/SL School 12', "contract_type" => "HL/SL", "lesson_duration" => 2, "show_paid_guidance" => 1, "representative_teacher_id" => 12, "phone_number" => "08836433643", "mst_textbook_category_ids" => "1,2,3,4,5,6,7,8,9", "created_at" => now()],
            [ "name" => 'HL/SL School 13', "contract_type" => "HL/SL", "lesson_duration" => 3, "show_paid_guidance" => 1, "representative_teacher_id" => 13, "phone_number" => "08836433643", "mst_textbook_category_ids" => 1, "created_at" => now()],
            [ "name" => 'HL/SL School 14', "contract_type" => "HL/SL", "lesson_duration" => 1, "show_paid_guidance" => 1, "representative_teacher_id" => 14, "phone_number" => "08836433643", "mst_textbook_category_ids" => 1, "created_at" => now()],
            [ "name" => 'HL/SL School 15', "contract_type" => "HL/SL", "lesson_duration" => 2, "show_paid_guidance" => 1, "representative_teacher_id" => 15, "phone_number" => "08836433643", "mst_textbook_category_ids" => 1, "created_at" => now()],
        ]);
    }
}
