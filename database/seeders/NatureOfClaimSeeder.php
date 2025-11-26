<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class NatureOfClaimSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
     //   try {
            DB::table("nature_of_claims")->insertOrIgnore([
                [
                    "claim" => "Employment",
                    "created_at" => now(),
                    "updated_at" => now(),
                ],
                [
                    "claim" => "Project Issues",
                    "created_at" => now(),
                    "updated_at" => now(),
                ],
                [
                    "claim" => "Accidents",
                    "created_at" => now(),
                    "updated" => now(),
                ],
                [
                    "claim" => "Competition",
                    "created_at" => now(),
                    "updated" => now(),
                ],
                [
                    "claim" => "Contract",
                    "created_at" => now(),
                    "updated" => now(),
                ],
            ]);
      //  } catch (\Throwable $th) {
            //throw $th;
      //
        //}
       
    }
}
