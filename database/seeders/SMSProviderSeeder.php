<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SMSProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (count(DB::table("sms_providers")->where("provider", "Twilio")->get()) == 0) {
            DB::table("sms_providers")->insert([
                "id" => 1,
                "provider" => "Twilio",
                "identifier" => "",
                "auth_token" => "",
                "active" => 0
            ]);
        }
        if (count(DB::table("sms_providers")->where("provider", "Africa's Talking")->get()) == 0) {
            DB::table("sms_providers")->insert([
                "id" => 2,
                "provider" => "Africa's Talking",
                "identifier" => "",
                "auth_token" => "",
                "active" => 0
            ]);
        }
        if (count(DB::table("sms_providers")->where("provider", "Sematime")->get()) == 0) {
            DB::table("sms_providers")->insert([
                "id" => 3,
                "provider" => "Sematime",
                "identifier" => "",
                "auth_token" => "",
                "active" => 0
            ]);
        }
    }
}
