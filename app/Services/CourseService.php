<?php
namespace App\Services;


use App\Models\Course;
use App\Models\CatgoryCourse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
class CourseService
{

      public function createCourse(array $data, $doctorId)
    {
        // معالجة رفع الفيديو أولاً
        if (isset($data['video'])) {
            $videoPath = $data['video']->store('courses/videos', 'public');
            $data['video_path'] = $videoPath;
            unset($data['video']);
        }

        return Course::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'video_path' => $data['video_path'] ?? null, // استخدام video_path بدلاً من video
            'category_id' => $data['category_id'],
            'doctor_id' => $doctorId,
            'is_active' => $data['is_active'] ?? true // إضافة حالة الكورس
        ]);
    }
     public function getCategories()
    {
        return CategoryCourse::all();
    }

     // عرض الكورسات حسب التصنيفات للمستخدمين
    public function getCoursesByCategoriesForUsers()
    {
        return CategoryCourse::with(['courses' => function($query) {
            $query->where('is_active', true);
        }])->get();
    }

     // عرض الكورسات حسب التصنيفات للأطباء
  public function getCoursesForDoctors($doctorId = null)
    {
        $doctorId = $doctorId ?? Auth::id();
        
        // تصحيح الاستعلام ليعيد كورسات الطبيب مباشرة
        return Course::where('doctor_id', $doctorId)->get();
    }

    public function uploadVideo($videoFile)
    {
        $path = $videoFile->store('courses/videos', 'public');
        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path)
        ];
    }
    
        public function getAllCourses()
    {
        return Course::with(['category', 'doctor'])->get();
    }

    /**
     * الحصول على الكورسات النشطة فقط (للمستخدمين العاديين)
     */
    public function getActiveCourses()
    {
        return Course::with(['category', 'doctor'])
            ->where('is_active', true)
            ->get();
    }

    /**
     * الحصول على كورسات طبيب معين
     */
    public function getDoctorCourses($doctorId)
    {
        return Course::with(['category'])
            ->where('doctor_id', $doctorId)
            ->get();
    }

}