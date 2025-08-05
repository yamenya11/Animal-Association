<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // 1. إنشاء الأدوار الأساسية
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $vetRole = Role::firstOrCreate(['name' => 'vet']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $ClientRole = Role::firstOrCreate(['name' => 'Client']);

        // 2. إنشاء جميع الصلاحيات المطلوبة
        $permissions = [
            'manage users',
            'view reports',
            'create animals',
            'edit animals',
            'delete animals',
            'view animals',
            'create posts',
            'delete posts',
            'comment posts',
            'like posts',
            'deposit wallet',
            'withdraw wallet',
            'view wallet balance',
            'request adoption',
            'respond adoption',
            'view adoptions',
            'apply volunteer',
            'respond volunteer',
            'view volunteer requests',
            'view notifications',
            'mark notifications read',
              'view all courses',
            'view active courses',
            'view doctor courses',
            'create courses',
            'view courses',
            'manage courses'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 3. تعيين الصلاحيات للأدوار
        $adminRole->syncPermissions(Permission::all());
        
        $vetRole->syncPermissions([
            'edit animals',
            'create animals',
            'view animals',
            'view reports',
             'view doctor courses', 
    'create courses',
    'manage courses'
        ]);
        
        $employeeRole->syncPermissions([
          'view reports',
    'edit animals',
    'view animals',
    'view volunteer requests',
    'respond volunteer'
        ]);
        
        $ClientRole->syncPermissions([
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
            'view animals'
        ]);

        // 4. إنشاء مستخدمين افتراضيين (اختياري)
        $this->createUser('Admin', 'admin@example.com', 'password', 'admin');
        $this->createUser('Vet', 'vet@example.com', 'password', 'vet');
        $this->createUser('Employee', 'employee@example.com', 'password', 'employee');
        $this->createUser('Client', 'Client@example.com', 'password', 'Client');
    }

    protected function createUser($name, $email, $password, $role)
    {
        if (!User::where('email', $email)->exists()) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
            $user->assignRole($role);
        }
    }
}