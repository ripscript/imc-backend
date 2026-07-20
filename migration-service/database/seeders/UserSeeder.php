<?php

namespace Database\Seeders;

use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exists = DB::table('users')->where('email', 'superadmin@gmail.com')->first();
        if (!$exists) {
            $role = DB::table('ms_roles')->where('name', 'Superadmin')->first();
            if (!$role) {
                throw new Exception('Role "Superadmin" tidak ditemukan');
            }
            DB::table('users')->insert([
                'name' => 'Superadmin',
                'email' => 'superadmin@gmail.com',
                'username' => 'superadmin',
                'password' => bcrypt('12345678'),
                'role_id' => $role->id,
                'gender' => 1,
                'status' => 1
            ]);
        }

        $exists = DB::table('users')->where('email', 'techteam@gmail.com')->first();
        if (!$exists) {
            $role = DB::table('ms_roles')->where('name', 'Superadmin')->first();
            if (!$role) {
                throw new Exception('Role "Superadmin" tidak ditemukan');
            }
            DB::table('users')->insert([
                'name' => 'Tim Teknis',
                'email' => 'techteam@gmail.com',
                'username' => 'techteam',
                'password' => bcrypt('TechTeam@DevPP1D!'),
                'role_id' => $role->id,
                'gender' => 1,
                'status' => 1
            ]);
        }
    }
}
