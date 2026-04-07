<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['admin', 'staff', 'customer'];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role],
                ['name' => $role]
            );
        }
    }
}
