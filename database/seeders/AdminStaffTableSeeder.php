<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminStaffTableSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = Role::where('name', 'admin')->value('id');
        $staffRoleId = Role::where('name', 'staff')->value('id');

        if ($adminRoleId) {
            User::updateOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'Admin',
                    'password' => Hash::make('123456'),
                    'phone_number' => '019999999',
                    'status' => 'active',
                    'avatar' => '',
                    'address' => 'Da Nang, Vietnam',
                    'role_id' => $adminRoleId,
                ]
            );
        }

        if ($staffRoleId) {
            User::updateOrCreate(
                ['email' => 'staff@example.com'],
                [
                    'name' => 'Staff',
                    'password' => Hash::make('123456'),
                    'phone_number' => '018889999',
                    'status' => 'active',
                    'avatar' => '',
                    'address' => 'Da Nang, Vietnam',
                    'role_id' => $staffRoleId,
                ]
            );
        }
    }
}
