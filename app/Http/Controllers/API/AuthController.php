<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use App\Models\Report;
use App\Models\User;
use App\Models\AnimalCase;
use Illuminate\Support\Facades\Storage; 
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
      private function formatDoctorData(User $doctor)
    {
        return [
            'id' => $doctor->id,
            'name' => $doctor->name,
            'email' => $doctor->email,
            'specialization' => $doctor->specialization,
            'experience' => $doctor->experience,
            'phone' => $doctor->phone,
            'bio' => $doctor->bio,
            'profile_image_url' => $doctor->profile_image 
                ? Storage::url($doctor->profile_image) 
                : null,
            'joined_at' => $doctor->created_at->format('Y-m-d')
        ];
    }

    // عرض الملف الشخصي للطبيب الحالي (معدل)
 public function showCurrentDoctorProfile()
{
    $doctor = Auth::user()->loadCount([
        'reports',
        'doctorCases as approved_cases_count' => function($query) {
            $query->where('approval_status', 'approved');
        },
        'reports as completed_reports_count' => function($query) {
            $query->where('status', 'completed');
        }
    ]);

    // جلب الحالات المعتمدة مع تفاصيلها
    $approvedCases = Auth::user()->doctorCases()
                        ->where('approval_status', 'approved')
                        ->with(['user', 'animal']) // إذا كنت تحتاج هذه العلاقات
                        ->get();

    return response()->json([
        'success' => true,
        'data' => [
            'profile' => $this->formatDoctorData($doctor),
            'stats' => [
                'total_reports' => $doctor->reports_count,
                'approved_cases' => $doctor->approved_cases_count,
                'completed_reports' => $doctor->completed_reports_count
            ],
            //'approved_cases' => $approvedCases // قائمة الحالات المعتمدة
        ]
    ]);
}
    // تحديث الملف الشخصي للطبيب
    public function updateDoctorProfile(Request $request)
    {
        $request->validate([
            'specialization' => 'sometimes|string|max:100',
            'experience' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'bio' => 'sometimes|string|max:500',
            'profile_image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $doctor = Auth::user();
        $data = $request->only(['specialization', 'experience', 'phone', 'bio']);

        if ($request->hasFile('profile_image')) {
            // حذف الصورة القديمة إذا وجدت
            if ($doctor->profile_image) {
                Storage::disk('public')->delete($doctor->profile_image);
            }
            $data['profile_image'] = $request->file('profile_image')->store('doctor_profiles', 'public');
        }

        $doctor->update($data);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الملف الشخصي بنجاح',
            'data' => $this->formatDoctorData($doctor->fresh())
        ]);
    }




}
