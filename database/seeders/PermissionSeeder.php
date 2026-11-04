<?php
namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'Lihat Dashboard', 'slug' => 'dashboard.view'],

            // Users
            ['name' => 'Lihat User',   'slug' => 'users.view'],
            ['name' => 'Buat User',    'slug' => 'users.create'],
            ['name' => 'Edit User',    'slug' => 'users.edit'],
            ['name' => 'Hapus User',   'slug' => 'users.delete'],

            // Roles
            ['name' => 'Lihat Role',   'slug' => 'roles.view'],
            ['name' => 'Buat Role',    'slug' => 'roles.create'],
            ['name' => 'Edit Role',    'slug' => 'roles.edit'],
            ['name' => 'Hapus Role',   'slug' => 'roles.delete'],

            // Permissions
            ['name' => 'Lihat Permission', 'slug' => 'permissions.view'],
            ['name' => 'Buat Permission',  'slug' => 'permissions.create'],
            ['name' => 'Edit Permission',  'slug' => 'permissions.edit'],
            ['name' => 'Hapus Permission', 'slug' => 'permissions.delete'],

            // Menus
            ['name' => 'Lihat Menu',   'slug' => 'menus.view'],
            ['name' => 'Buat Menu',    'slug' => 'menus.create'],
            ['name' => 'Edit Menu',    'slug' => 'menus.edit'],
            ['name' => 'Hapus Menu',   'slug' => 'menus.delete'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        // Assign semua permission ke role SuperAdmin
        $admin = Role::where('slug', 'superadmin')->first();
        if ($admin) {
            $admin->permissions()->sync(Permission::pluck('id')->all());
        }
    }
}
