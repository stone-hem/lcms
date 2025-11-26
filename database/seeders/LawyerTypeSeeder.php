<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class LawyerTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lawyer_types')->insert([
            array(
                'name'=>'civil',
                'description'=>'Disputes between individuals, organizations, or government entities, typically involving issues like contracts, property, or personal injury.',
                'created_at'=>now(),
                'updated_at'=>now()
            ),
            array(
                'name'=>'Corporate',
                'description'=>'Specializes in corporate law, which involves advising businesses on their legal rights, responsibilities, and obligations',
                'created_at'=>now(),
                'updated_at'=>now()
            ),
            array(
                'name'=>'Criminal',
                'description'=>'Prosecution by the government for an act that has been classified as a crime, such as theft, assault, or murder.',
                'created_at'=>now(),
                'updated'=>now()
            ),
        ]);
    }
}

