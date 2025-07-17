<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use App\Models\Report;
use App\Models\AnimalCase;
class AuthController extends Controller
{

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
    $response = $this->authService->register($request);
    return response()->json($response, 201);
    }

    public function login(Request $request)
{
    $response = $this->authService->login($request);
    return response()->json($response, 200);
}
   public function logout(Request $request)
{
    $response = $this->authService->logout($request);
    return response()->json($response, 200);
}

 // عرض ملف المستخدم
    public function profile()
    {
        $response = $this->authService->profile();
        return response()->json($response);
    }


    public function toggleAvailability()
{
    $user = Auth::user(); // أو User::find($id); لو كان الموظف من لوحة الإدارة
    $user->available = !$user->available;
    $user->save();

    return response()->json([
        'status' => true,
        'message' => 'تم تحديث حالة التوفر',
        'available' => $user->available
    ]);
}
 public function showCurrentDoctorProfile()
    {
        // الحصول على المستخدم الحالي (الطبيب المسجل)
        $doctor = Auth::user();
        
    

        // حساب الإحصائيات
        $stats = [
            'total_reports' => $doctor->reports()->count(),
            'total_cases' => $doctor->animal_cases()->count(),
            'completed_reports' => $doctor->reports()->where('status', 'Completed')->count(),
           // 'pending_cases' => $doctor->animal_cases()->where('status', 'approved')->count()
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'profile' => [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                    'email' => $doctor->email,
                    'specialization' => $doctor->specialization,
                    'profile_image' => $doctor->profile_image_url,
                    'phone' => $doctor->phone,
                    'joined_at' => $doctor->created_at->format('Y-m-d')
                ],
                'stats' => $stats
            ]
        ]);
    }


}
