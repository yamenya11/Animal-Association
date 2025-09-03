<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
class UserController extends Controller
{
 
     public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed', 
            'role' => 'required|in:admin,vet,employee,Client', 
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|digits:10|unique:users,phone',
            'experience' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'specialization' => 'nullable|string|max:255'
        ]);

        // إذا لم يكن الدور طبيب، اجعل الحقول الخاصة بالطبيب null
        if ($validated['role'] !== 'vet') {
            $validated['experience'] = null;
            $validated['bio'] = null;
            $validated['specialization'] = null;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'address' => $validated['address'] ?? 'not set',
            'phone' => $validated['phone'] ?? null,
            'experience' => $validated['experience'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
        ]);

      $user->assignRole($validated['role']);
$user->load('roles');

return response()->json([
    'status' => true,
    'message' => 'تم إضافة المستخدم بنجاح',
    'data' => [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'address' => $user->address,
        'phone' => $user->phone,
        'experience' => $user->experience,
        'bio' => $user->bio,
        'specialization' => $user->specialization,
        'roles' => $user->roles->pluck('name'), // فقط أسماء الأدوار
    ]
], 201);
    }

}
