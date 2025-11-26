<?php

namespace Database\Seeders;

use App\Models\CaseStage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CaseStageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // try {
        //     DB::table('case_stages')->insert([
        //         array('name' => 'Filing Stage'),
        //     ]);
        // } catch (\Throwable $th) {
        //     //throw $th;
        // }

        // try {
        //     DB::table('case_stages')->insert([
        //         array('name' => 'DG Stage'),
        //     ]);
        // } catch (\Throwable $th) {
        //     //throw $th;
        // }


        // try {
        //     DB::table('case_stages')->insert([
        //         array('name' => 'Procurement Stage'),
        //     ]);
        // } catch (\Throwable $th) {
        //     //throw $th;
        // }

        try {
            DB::table('case_stages')->insert([
                array('name' => 'Initiated'),
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }


        try {

            DB::table('case_stages')->insert([
                array('name' => 'Ongoing'),
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }


        try {
            DB::table('case_stages')->insert([
                array('name' => 'Appealed'),
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }

        try {

            DB::table('case_stages')->insert([
                array('name' => 'Concluded(won)'),
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
        try {

            DB::table('case_stages')->insert([
                array('name' => 'Concluded(lost)'),
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
