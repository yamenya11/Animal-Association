<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthService{

        public function register(Request $request): array
        {
            $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'phone' => 'nullable|digits:10|unique:users,phone',
                'level'    => 'nullable|string|max:255',
                'address'  => 'nullable|string|max:255',
            ]);

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => bcrypt($request->password),
                'phone'    => $request->phone,
                'level'    => $request->level ?? 'unknown',
                'address'  => $request->address ?? 'not set',
            ]);

            $user->assignRole('Client');

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'status' => true,
                'message' => 'تم التسجيل بنجاح.',
                'data' => [
                    'user' => [
                        'id'    => $user->id,
                        'name'  => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'level' => $user->level,
                        'address' => $user->address,
                        'role'  => $user->getRoleNames()->first(),
                    ],
                    'token' => $token,
                ],
            ];
        }



        public function login($request)
        {
            // التحقق من صحة البيانات المدخلة
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            // البحث عن المستخدم مع تحميل الأدوار والصلاحيات
            $user = User::where('email', $request->email)
                ->with(['roles', 'permissions'])
                ->first();

            // التحقق من وجود المستخدم وصحة كلمة المرور
            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة.'],
                ]);
            }

            // تحديث FCM Token إذا تم إرساله
            if ($request->filled('fcm_token')) {
                $this->updateFcmToken($user, $request->fcm_token);
            }

            // إنشاء token جديد للوصول
            $token = $user->createToken('auth_token')->plainTextToken;

            // تسجيل عملية تسجيل الدخول
            \Log::info('User logged in', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);

            return [
                'status' => true,
                'message' => 'تم تسجيل الدخول بنجاح.',
                'data' => [
                    'user' => $this->formatUserData($user),
                    'token' => $token,
                ],
            ];
        }

        protected function updateFcmToken($user, $fcmToken)
        {
            try {
                $user->update(['fcm_token' => $fcmToken]);
                \Log::info('FCM token updated', ['user_id' => $user->id]);
            } catch (\Exception $e) {
                \Log::error('Failed to update FCM token', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        protected function formatUserData($user)
        {
            return [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'roles'       => $user->roles->pluck('name'),
                'permissions' => $user->permissions->pluck('name'),
                // يمكن إضافة المزيد من الحقول حسب الحاجة
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


        public function profile():array
        {
            $userId = Auth::id();
            $userData = User::select([
                'name as user_name',
                'email as user_email',
                'wallet_balance',
                'experience',
                'region',
            ])->where('id', $userId)->first();

            if (!$userData) {
                return [
                    'status' => false,
                    'message' => 'المستخدم غير موجود',
                ];
            }

            return [
                'status' => true,
                'data' => $userData,
            ];

        }

 


}




























