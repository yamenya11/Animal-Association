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
  $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $vetRole = Role::firstOrCreate(['name' => 'vet']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // 2. تعريف الصلاحيات حسب التابع (دون تعديل الجدول)
        $userPermissions = [
            'manage users',
            'view reports',
        ];

        $animalPermissions = [
            'create animals',
            'edit animals',
            'delete animals',
            'view animals',
        ];

        $postPermissions = [
            'create posts',
            'delete posts',
            'comment posts',
            'like posts',
        ];

        $walletPermissions = [
            'deposit wallet',
            'withdraw wallet',
            'view wallet balance',
        ];

        $adoptionPermissions = [
            'request adoption',
            'respond adoption',
            'view adoptions',
        ];

        $volunteerPermissions = [
            'apply volunteer',
            'respond volunteer',
            'view volunteer requests',
        ];

        $notificationPermissions = [
            'view notifications',
            'mark notifications read',
        ];

        // 3. دمج جميع الصلاحيات
        $allPermissions = array_merge(
            $userPermissions,
            $animalPermissions,
            $postPermissions,
            $walletPermissions,
            $adoptionPermissions,
            $volunteerPermissions,
            $notificationPermissions
        );

        // 4. إنشاء الصلاحيات إن لم تكن موجودة
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 5. ربط الصلاحيات بالأدوار
        $adminRole->syncPermissions($allPermissions);

        $vetRole->syncPermissions([
            'edit animals',
            'create animals',
            'view animals',
            'view reports',
        ]);

        $employeeRole->syncPermissions([
            'view reports',
            'edit animals',
            'view animals',
            'view volunteer requests',
            'respond volunteer',
        ]);

        $userRole->syncPermissions([
            'request adoption',
            'create posts',
            'comment posts',
            'like posts',
            'apply volunteer',
            'view notifications',
            'mark notifications read',
            'deposit wallet',
            'withdraw wallet',
            'view wallet balance',
            'view animals',
        ]);

        // 6. إنشاء المستخدمين الافتراضيين لكل دور إذا لم يكونوا موجودين
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
            $employeeUser->assignRole($employeeRole);
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

