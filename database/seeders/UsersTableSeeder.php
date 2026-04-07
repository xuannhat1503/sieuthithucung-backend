<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $customerRoleId = Role::where('name', 'customer')->value('id');

        if (!$customerRoleId) {
            return;
        }

        $customers = [
            [
                'name' => 'Khach Hang Demo',
                'email' => 'customer@example.com',
                'password' => '123456',
                'phone_number' => '0900000001',
                'status' => 'active',
                'avatar' => '',
                'address' => 'Ho Chi Minh, Vietnam',
            ],
            [
                'name' => 'Khach Hang Cho Kich Hoat',
                'email' => 'pendingcustomer@example.com',
                'password' => '123456',
                'phone_number' => '0900000002',
                'status' => 'pending',
                'avatar' => '',
                'address' => 'Da Nang, Vietnam',
            ],
        ];

        foreach ($customers as $customer) {
            User::updateOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'password' => Hash::make($customer['password']),
                    'phone_number' => $customer['phone_number'],
                    'status' => $customer['status'],
                    'avatar' => $customer['avatar'],
                    'address' => $customer['address'],
                    'role_id' => $customerRoleId,
                ]
            );
        }
    }
}
