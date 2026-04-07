<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $staffRole = Role::where('name', 'staff')->first();
        $customerRole = Role::where('name', 'customer')->first();
        $permissions = Permission::all();

        if ($adminRole) {
            $adminRole->permissions()->sync($permissions->pluck('id')->all());
        }

        if ($staffRole) {
            $staffPermissionIds = $permissions
                ->whereIn('name', ['manage_products', 'manage_orders', 'manage_categories', 'manage_contacts'])
                ->pluck('id')
                ->all();

            $staffRole->permissions()->sync($staffPermissionIds);
        }

        if ($customerRole) {
            $customerRole->permissions()->sync([]);
        }
    }
}
