<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
class AuthService{

    function register ($request): array
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);
    
        $user = User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);
    
        $user->assignRole('user');
    
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return [
            'status' => true,
            'message' => 'تم التسجيل بنجاح.',
            'data' => [
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->getRoleNames()->first(),
                ],
                'token' => $token,
            ],
        ];
    }


function login($request){
  
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        
         ]);
         $user = User::where('email', $request->email)->first();

         if (!$user || !Hash::check($request->password, $user->password)) {
             throw ValidationException::withMessages([
                 'email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة.'],
             ]);
         }

         $role = $user->getRoleNames()->first();
         $token = $user->createToken('auth_token')->plainTextToken;
    return [
        'status' => true,
        'message' => ' التسجيل بنجاح.',
        'data' => [
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $role,
            ],
            'token' => $token,
        ],
    ];



}



public function logout(Request $request): array
{
    $request->user()->currentAccessToken()->delete();

    return [
        'status' => true,
        'message' => 'تم تسجيل الخروج بنجاح.',
    ];
}





}




























