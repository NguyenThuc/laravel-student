<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class ParentListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach(range(1, 15) as $item){
            DB::table('parents')->insert([
                ['email' => 'parent'. $item .'@gmail.com', 'first_name' => 'FParent' . $item, 'last_name' => 'LParent' . $item, 'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agree_terms' => 1, 'created_at' => now()],
            ]);
        }
    }
}
