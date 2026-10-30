<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'code' => 'super_admin',
                'name' => 'Superadmin',
            ],
            [
                'code' => 'admin_pusat',
                'name' => 'Admin Pusat',
            ],
            [
                'code' => 'admin_satker',
                'name' => 'Admin Satker/Perwakilan',
            ],
            [
                'code' => 'pimpinan',
                'name' => 'Pimpinan',
            ],
            [
                'code' => 'admin_pustik',
                'name' => 'Admin Pustik KP',
            ],
            [
                'code' => 'industri',
                'name' => 'Industri',
            ],
        ];

        foreach ($roles as $role) {
            // Update data lama
            DB::table('ms_roles')
                ->where('name', $role['name'])
                ->whereNull('code')
                ->update(['code' => $role['code']]);

            // Insert hanya jika code belum ada
            DB::table('ms_roles')
                ->where('code', $role['code'])
                ->doesntExist() && DB::table('ms_roles')->insert([
                    'code' => $role['code'],
                    'name' => $role['name'],
                ]);
        }
    }
}
