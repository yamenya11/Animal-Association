<?php
namespace App\Services;


use App\Models\Course;
use App\Models\CatgoryCourse;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
class CourseService
{

        public function createCourse(array $data, $doctorId)
        {
            return Course::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'video' => $data['video'], // لن يكون null لأننا نجبر على رفع الفيديو
                'duration' => $data['duration'],
                'category_id' => $data['category_id'],
                'doctor_id' => $doctorId,
                'is_active' => $data['is_active'] ?? true
            ]);
        }
        public function getCategories()
        {
            return CatgoryCourse::all(); // استخدام الاسم الأصلي
        }

            // عرض الكورسات حسب التصنيفات للمستخدمين
public function getCoursesByCategoriesForUsers($categoryName = null)
{
    return CatgoryCourse::query()
        ->when($categoryName, function($query) use ($categoryName) {
            $query->where('name', $categoryName);
        })
        ->with(['courses' => function($query) {
            $query->where('is_active', true)
                  ->with(['doctor:id,name'])
                  ->get()
                  ->map(function($course) {
                      $courseData = $course->toArray();
                      if ($course->video) {
                          $courseData['video_url'] = config('app.url') . '/storage/' . $course->video;
                      }
                      return $courseData;
                  });
        }])
        ->whereHas('courses', function($query) {
            $query->where('is_active', true);
        })
        ->get();
}

      public function getCoursesForDoctors($doctorId = null)
{
    $doctorId = $doctorId ?? Auth::id();
    
    return Course::with(['catgory'])
        ->where('doctor_id', $doctorId)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($course) {
            $courseData = $course->toArray();
            if ($course->video) {
                $courseData['video_url'] = config('app.url') . '/storage/' . $course->video;
            }
            return $courseData;
        });
}
        public function uploadVideo($videoFile)
        {
            try {
                $path = $videoFile->storeAs(
                    'courses/videos',
                    time().'_'.Str::random(20).'.'.$videoFile->extension(),
                    'public'
                );

                return $path; // نرجع المسار فقط (كمتغير نصي) بدلاً من مصفوفة
            } catch (\Exception $e) {
                Log::error('Video upload failed: '.$e->getMessage());
                throw new \Exception('فشل رفع الفيديو، حاول بملف أصغر');
            }
        }
            
        public function getAllCourses()
{
    return Course::with(['category', 'doctor'])
        ->select(['id', 'name', 'description', 'video', 'duration', 'category_id', 'doctor_id', 'is_active'])
        ->get()
        ->map(function($course) {
            $courseData = $course->toArray();
            if ($course->video) {
                $courseData['video_url'] = config('app.url') . '/storage/' . $course->video;
            }
            return $courseData;
        });
}



    /**
     * الحصول على الكورسات النشطة فقط (للمستخدمين العاديين)
     */
 public function getActiveCourses()
{
    return Course::with(['category', 'doctor'])
        ->select(['id', 'name', 'description', 'video', 'duration', 'category_id', 'doctor_id'])
        ->where('is_active', true)
        ->get()
        ->map(function($course) {
            $courseData = $course->toArray();
            if ($course->video) {
                $courseData['video_url'] = config('app.url') . '/storage/' . $course->video;
            }
            return $courseData;
        });
}

    /**
     * الحصول على كورسات طبيب معين
     */
 public function getDoctorCourses($doctorId)
{
    return Course::with(['category'])
        ->select(['id', 'name', 'description', 'video', 'duration', 'category_id', 'is_active'])
        ->where('doctor_id', $doctorId)
        ->get()
        ->map(function($course) {
            $courseData = $course->toArray();
            if ($course->video) {
                $courseData['video_url'] = config('app.url') . '/storage/' . $course->video;
            }
            return $courseData;
        });
}

public function deleteCourse($id, $user)
{
    $course = Course::findOrFail($id);

    // // التحقق من الصلاحيات
    // if (!$user->hasRole('admin') && !($user->hasRole('vet') && $course->doctor_id == $user->id)) {
    //     throw new \Exception('غير مصرح بهذا الإجراء');
    // }

    // حذف الفيديو المرتبط
    if ($course->video) {
        Storage::disk('public')->delete($course->video);
    }

    return $course->delete();
}

}