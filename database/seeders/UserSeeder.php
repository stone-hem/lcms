<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use PhpParser\Node\Stmt\TryCatch;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

            DB::table('users')->insert([
                array(
                    'role_id' => 1,
                    'name' => 'Super Admin',
                    'first_name' => 'Super',
                    'last_name' => 'Admin',
                    'email' => 'superadmin@admin.com',
                    'email_verified_at' => '2023-04-08 20:20:20',
                    'phone_verified_at' => '2023-04-08 20:20:20',
                    'password' => \Illuminate\Support\Facades\Hash::make('$$RT55TYXD'),
                    'calling_code' => '254',
                    'phone' => '254743621073',
                    'can_login' => 1,
                    'is_active' => 1,
                ),
                
            ]);
       

    }
}
