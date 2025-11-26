<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        SiteSetting::upsert([
            "id" => 1,
            "name" => "KenHA",
            "site_logo_path" => "https://kenha.co.ke/wp-content/uploads/2021/07/Website-Logo.png",
            "emails" => json_encode([
                ["contact_us" => "complaints@kenha.co.ke"]
            ])
        ], uniqueBy: ["id"]);
    }
}
