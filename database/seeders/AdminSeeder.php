<?php

namespace Database\Seeders;

use App\Models\Admins\Admin;
use App\Models\Admins\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */  public function run()
    {
        $roles = Role::whereIn('name', ['Super Admin', 'Admin', 'Operator'])->pluck('id', 'name');

        $admins = [
            ['name' => 'John Doe', 'email' => 'superadmin@gmail.com', 'role' => 'Super Admin'],
            ['name' => 'Alice Smith', 'email' => 'alice@example.com', 'role' => 'Admin'],
            ['name' => 'Bob Johnson', 'email' => 'bob@example.com', 'role' => 'Admin'],
            ['name' => 'Charlie Brown', 'email' => 'charlie@example.com', 'role' => 'Operator'],
            ['name' => 'David White', 'email' => 'david@example.com', 'role' => 'Operator'],
        ];

        foreach ($admins as $admin) {
            Admin::create([
                'name' => $admin['name'],
                'email' => $admin['email'],
                'password' => Hash::make('password'),
                'role_id' => $roles[$admin['role']],
                'photo' => null
            ]);
        }
    }
}
