<?php

namespace Rupadana\ApiService\Tests\Fixtures\Database\Seeders;

use Illuminate\Database\Seeder;
use Rupadana\ApiService\Tests\Fixtures\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        $user = User::create([
            'name' => 'Rupadana',
            'email' => 'rupadanawayan@gmail.com',
            'password' => bcrypt('12345678'),
        ]);

        $role = Role::create([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        collect([
            'view',
            'view_any',
            'create',
            'update',
            'delete',
        ])
            ->each(function ($name) use ($role) {
                $permission = Permission::create([
                    'name' => "{$name}_product",
                    'guard_name' => 'web',
                ]);

                $role->givePermissionTo($permission);
            });

        $user->assignRole('super_admin');
    }
}
