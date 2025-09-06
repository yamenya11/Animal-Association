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
                    'video' => $data['video'], 
                    'duration' => $data['duration'],
                    'category_id' => $data['category_id'],
                    'doctor_id' => $doctorId,
                    'is_active' => $data['is_active'] ?? true
                ]);
            }
            public function getCategories()
            {
                return CatgoryCourse::all();
            }

                
    public function getCoursesByCategoriesForUsers($categoryName = null)
    {
        $categories = CatgoryCourse::query()
            ->when($categoryName, function($query) use ($categoryName) {
                $query->where('name', $categoryName);
            })
            ->with(['courses' => function($query) {
                $query->where('is_active', true)
                    ->with(['doctor:id,name']);
            }])
            ->whereHas('courses', function($query) {
                $query->where('is_active', true);
            })
            ->get()
            ->map(function($category) {
                $categoryData = $category->toArray();

                $categoryData['courses'] = collect($categoryData['courses'])->map(function($course) {
                    $course['video_url'] = $course['video'] ? config('app.url') . '/storage/' . $course['video'] : null;
                    return $course;
                });

                return $categoryData;
            });

        return $categories;
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

                    return $path;
                } catch (\Exception $e) {
                    Log::error('Video upload failed: '.$e->getMessage());
                    throw new \Exception('فشل رفع الفيديو، حاول بملف أصغر');
                }
            }
                
    //         public function getAllCourses()
    //     {
    //     return Course::with(['category', 'doctor'])
    //         ->select(['id', 'name', 'description', 'video', 'duration', 'category_id', 'doctor_id', 'is_active'])
    //         ->get()
    //         ->map(function($course) {
    //             $courseData = $course->toArray();
    //             if ($course->video) {
    //                 $courseData['video_url'] = config('app.url') . '/storage/' . $course->video;
    //             }
    //             return $courseData;
    //         });
    //    }



    public function getActiveCourses()
    {
        $courses = Course::with(['category:id,name','doctor:id,name'])
            ->select(['id','name','description','video','duration','category_id','doctor_id'])
            ->where('is_active', true)
            ->get()
            ->map(function($course) {
                return [
                    'id' => $course->id,
                    'name' => $course->name,
                    'description' => $course->description,
                    'duration' => $course->duration,
                    'video' => $course->video,
                    'video_url' => $course->video ? config('app.url') . '/storage/' . $course->video : null,
                    'category_id' => $course->category_id,
                    'category_name' => $course->category->name ?? null,
                    'doctor_id' => $course->doctor_id,
                    'doctor_name' => $course->doctor->name ?? null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $courses
        ]);
    }




    public function deleteCourse($id, $user)
    {
        $course = Course::findOrFail($id);

     
        // if (!$user->hasRole('admin') && !($user->hasRole('vet') && $course->doctor_id == $user->id)) {
        //     throw new \Exception('غير مصرح بهذا الإجراء');
        // }

    
        if ($course->video) {
            Storage::disk('public')->delete($course->video);
        }

        return $course->delete();
    }
    public function addView($courseId, $userId)
    {
        try {
            $course = Course::findOrFail($courseId);

            $interaction = $course->users()->where('user_id', $userId)->first();

            if ($interaction) {
                $course->users()->updateExistingPivot($userId, [
                    'last_watched_at' => now(),
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
            public function getLikes($courseId)
            {
                $course = Course::withCount('likes')->with('likes.user:id,name')->findOrFail($courseId);

                return [
                    'total_likes' => $course->likes_count,
                    'users'       => $course->likes->pluck('user')
                ];
            }

    //احصائيات كورس محدد
    public function getCourseStats($courseId, $doctorId = null)
    {
        $course = Course::with([
            'users' => function($query) {
                $query->select('users.id', 'users.name', 'users.email');
            },
            'ratings.user'  
        ])->findOrFail($courseId);

        if ($doctorId && $course->doctor_id != $doctorId) {
            throw new \Exception('غير مصرح بالوصول إلى إحصائيات هذا الكورس');
        }

        return [
            'course_id' => $course->id,
            'course_name' => $course->name,

            'total_views' => $course->users()->sum('video_views'),
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
                        'video_views' => $user->pivot->video_views,
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
                }),

            'average_rating' => $course->ratings->avg('rating'),
            'ratings_count' => $course->ratings->count(),
            'recent_ratings' => $course->ratings()
                ->latest()
                ->limit(5)
                ->get()
                ->map(function($rating) {
                    return [
                        'user_id' => $rating->user->id,
                        'user_name' => $rating->user->name,
                        'rating' => $rating->rating,
                        'review' => $rating->review,
                        'created_at' => $rating->created_at,
                    ];
                }),
        ];
    }
        public function getDoctorStats($doctorId)
        {
            $courses = Course::with(['ratings.user', 'users'])->where('doctor_id', $doctorId)->get();

            $totalRatings = 0;
            $totalReviews = 0;
            $ratingsSum = 0;

            $recentRatings = collect();

            foreach ($courses as $course) {
                $totalRatings += $course->ratings->count();
                $ratingsSum += $course->ratings->sum('rating');
                $totalReviews += $course->ratings->whereNotNull('review')->count();

                $recentRatings = $recentRatings->concat(
                    $course->ratings->sortByDesc('created_at')->take(5)->map(function($rating) {
                        return [
                            'course_id' => $rating->course_id,
                            'user_id' => $rating->user->id,
                            'user_name' => $rating->user->name,
                            'rating' => $rating->rating,
                            'review' => $rating->review,
                            'created_at' => $rating->created_at,
                        ];
                    })
                );
            }

            return [
                'success' => true,
                'statistics' => [
                    'total_ratings' => $totalRatings,
                    'average_rating' => $totalRatings ? round($ratingsSum / $totalRatings, 1) : 0,
                    'total_reviews' => $totalReviews,
                    'total_views' => $courses->sum(function($course) {
                        return $course->users->sum('video_views');
                    }),
                    'total_unique_viewers' => $courses->sum(function($course) {
                        return $course->users->count();
                    }),
                ],
                'recent_ratings' => $recentRatings->take(5)->values(),
            ];
        }




}