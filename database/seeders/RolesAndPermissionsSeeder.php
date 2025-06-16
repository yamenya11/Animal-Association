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
       $employeeRole = Role::firstOrCreate(['name' => 'employee']);
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
        $employeeRole->syncPermissions(['view reports', 'edit animals']); // مثال


        // 4. إنشاء مدير واحد فقط إذا لم يكن موجودًا
       if (!User::where('email', 'admin@example.com')->exists()) {
            $adminUser = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);
            $adminUser->assignRole($adminRole);
        }
        
        
        if (!User::where('email', 'vet@example.com')->exists()) {
            $vetUser = User::factory()->create([
                'name' => 'Vet User',
                'email' => 'vet@example.com',
                'password' => bcrypt('password'),
            ]);
            $vetUser->assignRole($vetRole);
        }




    if (!User::where('email', 'staff@example.com')->exists()) {
    $employeeUser = User::factory()->create([
        'name' => 'Employee User',
        'email' => 'staff@example.com',
        'password' => bcrypt('password'),
    ]);

    $employeeUser->assignRole('employee'); // تأكد أن الدور موجود
}


        if (!User::where('email', 'user@example.com')->exists()) {
            $normalUser = User::factory()->create([
                'name' => 'Normal User',
                'email' => 'user@example.com',
                'password' => bcrypt('password'),
            ]);
            $normalUser->assignRole($userRole);
        }
    }
}
