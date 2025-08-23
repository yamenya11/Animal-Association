<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CourseService;
use App\Models\Course;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\CatgoryCourse;
use Illuminate\Support\Facades\Storage;
class CourseController extends Controller
{
  
    protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'video' => 'required|file|mimes:mp4,mov,avi|max:50000',
        'duration' => 'required|string',
        'category_name' => 'required|string',
    ]);

    $category = CatgoryCourse::where('name', $request->category_name)->firstOrFail();

    try {
        // رفع الفيديو (سيتم إرجاع المسار فقط)
        $videoPath = $this->courseService->uploadVideo($request->file('video'));
        
        // إنشاء الكورس
        $course = $this->courseService->createCourse([
            'name' => $request->name,
            'description' => $request->description,
            'video' => $videoPath, // استخدام video بدلاً من video_path
            'duration' => $request->duration,
            'category_id' => $category->id,
            'is_active' => $request->boolean('is_active', true)
        ], auth()->id());

        return response()->json([
            'success' => true,
            'data' => $course,
            'video_url' => Storage::disk('public')->url($videoPath) // إنشاء الرابط هنا
        ], 201);

    } catch (\Exception $e) {
        if (isset($videoPath)) {
            Storage::disk('public')->delete($videoPath);
        }
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

    // عرض جميع الكورسات (للمستخدمين العاديين - النشطة فقط)
    public function indexForUsers()
    {
        $courses = $this->courseService->getActiveCourses();
        return response()->json($courses);
    }

    // عرض الكورسات حسب التصنيفات (للمستخدمين)
  public function getByCategories(Request $request)
{
    $categoryName = $request->input('category'); // أو يمكن استخدام route parameter
    
    $courses = $this->courseService->getCoursesByCategoriesForUsers($categoryName);
    
    return response()->json([
        'success' => true,
        'data' => $courses
    ]);
}

    // عرض كورسات الطبيب (للطبيب نفسه)
public function indexForDoctor()
{
    $doctorId = auth()->id();
    
    $courses = Course::with(['category', 'doctor']) // تأكد أنه يستخدم النموذج الصحيح
        ->where('doctor_id', $doctorId)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($course) {
            return [
                'id' => $course->id,
                'name' => $course->name,
                'description' => $course->description,
                'video_url' => $course->video ? Storage::disk('public')->url($course->video) : null,
                'duration' => $course->duration,
                'category' => $course->category->name ?? null,
                'doctor_name' => $course->doctor->name ?? null,
                'is_active' => (bool)$course->is_active,
                'created_at' => $course->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $course->updated_at->format('Y-m-d H:i:s')
            ];
        });

    return response()->json([
        'success' => true,
        'data' => $courses
    ]);
}
// public function getCoursesForDoctors($doctorId = null, $perPage = 10)
// {
//     $doctorId = $doctorId ?? Auth::id();
    
//     return Course::with(['category', 'doctor'])
//         ->where('doctor_id', $doctorId)
//         ->orderBy('created_at', 'desc')
//         ->paginate($perPage);
// }
    // عرض جميع الكورسات (للمسؤولين فقط)
    // public function indexForAdmin()
    // {
    //     $courses = $this->courseService->getAllCourses();
    //     return response()->json($courses);
    // }

public function destroy($id)
{
    $course = Course::findOrFail($id);
    $user = auth()->user();

    // التحقق المباشر من الصلاحيات
    if (!$user->hasRole('admin') && !($user->hasRole('vet') && $course->doctor_id == $user->id)) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح بهذا الإجراء'
        ], 403);
    }

    // حذف الفيديو من التخزين إذا كان موجودًا
    if ($course->video && Storage::disk('public')->exists($course->video)) {
        Storage::disk('public')->delete($course->video);
    }

    $course->delete();

    return response()->json([
        'success' => true,
        'message' => 'تم حذف الكورس بنجاح'
    ]);
}
}
