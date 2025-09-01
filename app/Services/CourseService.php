<?php
namespace App\Services;


use App\Models\Course;
use App\Models\CatgoryCourse;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
    return Course::with([
        'category:id,name', // فقط ID واسم التصنيف
        'doctor:id,name'    // فقط ID واسم الطبيب
    ])
    ->select(['id', 'name', 'description', 'video', 'duration', 'category_id', 'doctor_id'])
    ->where('is_active', true)
    ->get()
    ->map(function($course) {
        $courseData = $course->toArray();
        if ($course->video) {
            $courseData['video_url'] = config('app.url') . '/storage/' . $course->video;
        }
        
        // إضافة اسم الطبيب فقط إذا كان موجوداً
        $courseData['doctor_name'] = $course->doctor->name ?? null;
        
        // إضافة اسم التصنيف فقط إذا كان موجوداً
        $courseData['category_name'] = $course->category->name ?? null;
        
        // إزالة البيانات الزائدة
        unset($courseData['doctor'], $courseData['category']);
        
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
public function addView($courseId, $userId)
{
    try {
        $course = Course::findOrFail($courseId); // البحث عن الكورس الصحيح
        
        $interaction = $course->users()->where('user_id', $userId)->first();
        
        if ($interaction) {
            $course->users()->updateExistingPivot($userId, [
                'last_watched_at' => now()
            ]);
            
            return [
                'success' => true, 
                'video_views' => $interaction->pivot->video_views,
                'message' => 'تم تحديث وقت المشاهدة فقط',
                'counted' => false
            ];
        }
        
        $course->users()->attach($userId, [
            'video_views' => 1,
            'is_liked' => false,
            'last_watched_at' => now()
        ]);
        
        return [
            'success' => true, 
            'video_views' => 1,
            'message' => 'تم تسجيل المشاهدة الأولى بنجاح',
            'counted' => true
        ];
        
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'حدث خطأ: ' . $e->getMessage()
        ];
    }
}
public function toggleLike($courseId, $userId)
{
    return DB::transaction(function () use ($courseId, $userId) {
        $course = Course::findOrFail($courseId);
        $existingInteraction = $course->users()->where('user_id', $userId)->first();
        
        if ($existingInteraction) {
            $newLikeStatus = !$existingInteraction->pivot->is_liked;
            
            $course->users()->updateExistingPivot($userId, [
                'is_liked' => $newLikeStatus
            ]);
            
            return [
                'success' => true,
                'is_liked' => $newLikeStatus,
                'action' => $newLikeStatus ? 'liked' : 'unliked'
            ];
        } else {
            $course->users()->attach($userId, [
                'is_liked' => true,
                'video_views' => 0, 
                'last_watched_at' => now()
            ]);
            
            return [
                'success' => true,
                'is_liked' => true,
                'action' => 'liked'
            ];
        }
    });
}

     public function getCourseStats($courseId, $doctorId = null)
    {
        $course = Course::with(['users' => function($query) {
            $query->select('users.id', 'users.name', 'users.email');
        }])->findOrFail($courseId);

        // التحقق إذا الطبيب هو صاحب الكورس
        if ($doctorId && $course->doctor_id != $doctorId) {
            throw new \Exception('غير مصرح بالوصول إلى إحصائيات هذا الكورس');
        }

        return [
            'course_id' => $course->id,
            'course_name' => $course->name,
            'total_views' => $course->users()->sum('views'),
            'total_likes' => $course->users()->wherePivot('is_liked', true)->count(),
            'total_unique_viewers' => $course->users()->count(),
            'recent_views' => $course->users()
                ->orderBy('pivot_last_watched_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($user) {
                    return [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'views' => $user->pivot->views,
                        'last_watched' => $user->pivot->last_watched_at
                    ];
                }),
            'liked_users' => $course->users()
                ->wherePivot('is_liked', true)
                ->get()
                ->map(function($user) {
                    return [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'email' => $user->email
                    ];
                })
        ];
    }

      public function getDoctorStats($doctorId)
    {
        $courses = Course::withCount([
                'users as total_views' => function($query) {
                    $query->select(DB::raw('SUM(views)'));
                },
                'users as total_likes' => function($query) {
                    $query->where('is_liked', true);
                },
                'users as total_unique_viewers'
            ])
            ->where('doctor_id', $doctorId)
            ->get();

        return [
            'total_courses' => $courses->count(),
            'total_views_all_courses' => $courses->sum('total_views'),
            'total_likes_all_courses' => $courses->sum('total_likes'),
            'total_unique_viewers_all_courses' => $courses->sum('total_unique_viewers'),
            'courses' => $courses->map(function($course) {
                return [
                    'course_id' => $course->id,
                    'course_name' => $course->name,
                    'views' => $course->total_views,
                    'likes' => $course->total_likes,
                    'unique_viewers' => $course->total_unique_viewers
                ];
            })
        ];
    }

}