<?php

namespace Database\Seeders;

use App\Models\Admins\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $roles = ['Super Admin', 'Admin', 'Operator', 'Siswa', 'Guru'];

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }
    }
}
