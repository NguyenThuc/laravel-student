<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Agency;
use App\Models\Seller;

class SellerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Agency::updateOrCreate(
            ["id" => 1],
            [
                "name" => "代理店１"
            ]
        );

        foreach(range(1, 15) as $id){
            Seller::firstOrCreate(
                ["id" => $id],
                [
                    "agency_id" => 1,
                    "name" => '山田' . $id,
                    "email" => 'seller'. $id .'@westacton.com.jp',
                    "password" => Hash::make('Password'),
                    "role" => 1,
                ]
            );
        }
    }
}
