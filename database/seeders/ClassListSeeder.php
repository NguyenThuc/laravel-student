<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class ClassListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach(range(1, 15) as $item){
            DB::table('class_lists')->insert([
                ['school_id' => $item, 'mst_course_id' => 1, 'mst_year_id' => 1, 'mst_class_id' => 1, 'fiscal_year' => '2022', 'created_at' => now()],
                ['school_id' => $item, 'mst_course_id' => 2, 'mst_year_id' => 2, 'mst_class_id' => 2, 'fiscal_year' => '2022', 'created_at' => now()]
            ]);
        }
    }
}
