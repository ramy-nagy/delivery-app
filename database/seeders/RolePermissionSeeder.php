<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guardName = 'web';

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => $guardName],
        );

        $resources = ['restaurant', 'shop'];
        $abilities = ['viewAny', 'view', 'create', 'update', 'delete', 'restore'];

        $permissions = [];
        foreach ($resources as $resource) {
            foreach ($abilities as $ability) {
                $permissions[] = Permission::firstOrCreate(
                    ['name' => $resource . '.' . $ability, 'guard_name' => $guardName]
                );
            }
        }

        // Give admin all CRUD permissions for vendor models.
        $adminRole->syncPermissions($permissions);

        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        $adminPassword = env('ADMIN_PASSWORD', 'password');
        $adminName = env('ADMIN_NAME', 'Admin');

        /** @var User $user */
        $user = User::query()->firstOrCreate(
            ['email' => $adminEmail],
            ['name' => $adminName, 'password' => Hash::make($adminPassword)]
        );

        $user->syncRoles([$adminRole->name]);
    }
}

