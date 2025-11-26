<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            CaseStageSeeder::class,
            PartyTypeSeeder::class,
            CaseTypeSeeder::class,
            LocationSeeder::class,
            LawyerTypeSeeder::class,
            NatureOfClaimSeeder::class,
            SMTPConfigTypeSeeder::class,
            SMSProviderSeeder::class,
            SiteSettingsSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            PermissionSeeder::class,
            CountriesSeeder::class,
            MetaRuleSeeder::class,
            EventCategorySeeder::class,
        ]);
    }
}
