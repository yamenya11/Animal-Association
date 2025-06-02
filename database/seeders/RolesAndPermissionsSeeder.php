<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // 1. إنشاء الأدوار
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $vetRole = Role::firstOrCreate(['name' => 'vet']);
        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // 2. إنشاء الصلاحيات
        $permissions = [
            'manage users',
            'edit animals',
            'view reports',
            'delete animals',
            'create animals',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 3. ربط الصلاحيات بالأدوار
        $adminRole->syncPermissions($permissions);
        $vetRole->syncPermissions(['edit animals', 'create animals', 'view reports']);
        $userRole->syncPermissions(['view reports']);

        // 4. إنشاء مدير واحد فقط إذا لم يكن موجودًا
        $adminEmail = 'admin@example.com';
        if (!User::where('email', $adminEmail)->exists()) {
            $adminUser = User::factory()->create([
                'name' => 'Admin User',
                'email' => $adminEmail,
                'password' => bcrypt('password'),
            ]);
            $adminUser->assignRole($adminRole);
        }  
    }
}
