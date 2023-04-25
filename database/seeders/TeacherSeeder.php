<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Teacher;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $randPassword = 'Teacher@2022';

        foreach(range(1, 15) as $id){
            Teacher::create([
                "school_id" => $id,
                "first_name" => 'Fname' . $id,
                "last_name" => 'Lname' . $id,
                "email" => 'teacher'. $id .'@westacton.com.jp',
                "password" => Hash::make($randPassword),
                "role" => 1,
                "is_verified" => 0,
                "password_status" => 0
            ]);
        }
    }
}
