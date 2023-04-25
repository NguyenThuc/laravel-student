<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MstClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach(range(1, 15) as $item){
            \DB::table('mst_classes')->insert([
                ['school_id' => $item, 'name' => 'Class 1', 'created_at' => now()],
                ['school_id' => $item, 'name' => 'Class 2', 'created_at' => now()],
                ['school_id' => $item, 'name' => 'Class 3', 'created_at' => now()],
                ['school_id' => $item, 'name' => 'Class 4', 'created_at' => now()],
                ['school_id' => $item, 'name' => 'Class 5', 'created_at' => now()],
            ]);
        }
    }
}
