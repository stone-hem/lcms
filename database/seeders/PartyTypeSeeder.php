<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PartyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            DB::table("party_types")->insert([
                [
                    "name" => "Respondent",
                    "created_at" => now(),
                    "updated_at" => now(),
                ],
                [
                    "name" => "Plaintiff",
                    "created_at" => now(),
                    "updated_at" => now(),
                ],
                [
                    "name" => "Applicant",
                    "created_at" => now(),
                    "updated" => now(),
                ],
                [
                    "name" => "Defendant respondent",
                    "created_at" => now(),
                    "updated" => now(),
                ],
                [
                    "name" => "Interested party",
                    "created_at" => now(),
                    "updated" => now(),
                ],
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
