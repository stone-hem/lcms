<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            DB::table('event_categories')->insert([array('id' => 1, 'category' => 'Pre-trial'),]);
        } catch (\Throwable $th) {
        }
        try {
            DB::table('event_categories')->insert([array('id' => 2, 'category' => 'Mention'),]);
        } catch (\Throwable $th) {
        }
        try {
            DB::table('event_categories')->insert([array('id' => 3, 'category' => 'Hearing'),]);
        } catch (\Throwable $th) {
        }
        try {
            DB::table('event_categories')->insert([array('id' => 4, 'category' => 'Ruling'),]);
        } catch (\Throwable $th) {
        }
        try {
            DB::table('event_categories')->insert([array('id' => 5, 'category' => 'Judgement'),]);
        } catch (\Throwable $th) {
        }
        try {
            DB::table('event_categories')->insert([array('id' => 5, 'category' => 'Site visit'),]);
        } catch (\Throwable $th) {
        }
    }
}
