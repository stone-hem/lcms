<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
     {
         DB::table("roles")->insert([
             ["id" => 1, "name" => "Admin", "guard_name" => "api"],
         ]);
         DB::table("roles")->insert([
             ["id" => 2, "name" => "Lawyer", "guard_name" => "api"],
         ]);
         DB::table("roles")->insert([
             ["id" => 3, "name" => "External counsel", "guard_name" => "api"],
         ]);
         DB::table("roles")->insert([
             ["id" => 4, "name" => "Director General", "guard_name" => "api"],
         ]);
     }
}
