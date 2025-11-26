<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class CaseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            DB::table('case_types')->updateOrInsert([
                array(
                    'name' => 'Civil',
                    'description' => 'Disputes between individuals, organizations, or government entities, typically involving issues like contracts, property, or personal injury.',
                    'created_at' => now(),
                    'updated_at' => now()
                )
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
        try {

            DB::table('case_types')->insert([
                array(
                    'name' => 'Criminal',
                    'description' => 'Prosecution by the government for an act that has been classified as a crime, such as theft, assault, or murder.',
                    'created_at' => now(),
                    'updated' => now()
                ),
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }


        try {
            DB::table('case_types')->insert([
                array(
                    'name' => 'Traffic',
                    'description' => 'Specializes in trafic law, which involves advising drivers on their legal rights, responsibilities, and obligations',
                    'created_at' => now(),
                    'updated_at' => now()
                ),

            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
