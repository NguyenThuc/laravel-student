<?php

namespace Database\Seeders;

use App\Models\MstTextbookCategory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        $this->call([
//            MstYearSeeder::class,
//            TeacherSeeder::class,
//            SchoolSeeder::class,
//            SchoolReservationSlotSeeder::class,
//            MstTextbookCategorySeeder::class,
            SellerSeeder::class,
        ]);
    }
}
