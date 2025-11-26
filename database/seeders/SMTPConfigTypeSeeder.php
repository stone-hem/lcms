<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SMTPConfigTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        if (count(DB::table("smtp_config_types")->where("id", 1)->get()) == 0) {
            DB::table("smtp_config_types")->insert([
                "id"=>1,
                "type" => "General",
                "is_active"=>true,
            ]);
        }
    }
}
