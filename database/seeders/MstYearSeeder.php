<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MstYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('mst_years')->insert([
            ['name' => '小学5年'],
            ['name' => '小学6年'],
            ['name' => '中学1年'],
            ['name' => '中学2年'],
            ['name' => '中学3年'],
            ['name' => '高校1年'],
            ['name' => '高校2年'],
            ['name' => '高校3年'],
            ['name' => 'その他']
        ]);
    }
}
