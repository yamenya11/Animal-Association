<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Course;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class RatingController extends Controller
{
      public function store(Request $request, $courseId)
    {
        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'review' => 'nullable|string|max:1000' // ← **تعليق على جودة الكورس**
        ]);
        
        $course = Course::findOrFail($courseId);
        
        // التحقق إذا كان المستخدم قد قام بالتقييم مسبقاً
        $existingRating = Rating::where('user_id', Auth::id())
            ->where('course_id', $courseId)
            ->first();
            
        if ($existingRating) {
            return response()->json([
                'success' => false,
                'message' => 'لقد قمت بتقييم هذا الكورس مسبقاً'
            ], 422);
        }
        
        // إنشاء التقييم
        $rating = Rating::create([
            'user_id' => Auth::id(),
            'course_id' => $courseId,
            'rating' => $request->rating,
            'review' => $request->review // ← **تعليق المستخدم على الكورس**
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'تم إضافة التقييم بنجاح',
            'rating' => $rating
        ]);
    }
     public function update(Request $request, $ratingId)
    {
        $rating = Rating::where('user_id', Auth::id())
            ->findOrFail($ratingId);
            
        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'review' => 'nullable|string|max:1000' // ← **تعديل التعليق**
        ]);
        
        $rating->update([
            'rating' => $request->rating,
            'review' => $request->review
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'تم تحديث التقييم بنجاح',
            'rating' => $rating
        ]);
    }

        public function destroy($ratingId)
    {
           if (!Auth::user()->hasRole('vet')) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بهذا الإجراء'
            ], 403);
        }
        $rating = Rating::findOrFail($ratingId);
        $rating->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'تم حذف التقييم بنجاح'
        ]);
    }
    public function getCourseRatings($courseId)
    {
        $ratings = Rating::with('user')
            ->where('course_id', $courseId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return response()->json($ratings);
    }
      public function getUserRating($courseId)
    {
        $rating = Rating::where('user_id', Auth::id())
            ->where('course_id', $courseId)
            ->first();
            
        return response()->json($rating);
    }

    public function adminIndex()
{
    if (!Auth::user()->hasRole('vet')) {
        return response()->json([
            'success' => false,
            'message' => 'غير مصرح لك بهذا الإجراء'
        ], 403);
    }
    
    // الحصول على التقييمات مع الت pagination
    $ratings = Rating::with(['user', 'course'])
        ->orderBy('created_at', 'desc')
        ->paginate(20);
    
    // حساب الإحصائيات الإجمالية
    $totalRatings = Rating::count();
    $averageRating = Rating::avg('rating') ?: 0;
    $totalReviews = Rating::whereNotNull('review')->count();
    
    // توزيع التقييمات (عدد كل نجمة)
    $ratingDistribution = Rating::selectRaw('rating, count(*) as count')
        ->groupBy('rating')
        ->orderBy('rating', 'desc')
        ->get()
        ->mapWithKeys(function ($item) {
            return [$item->rating => $item->count];
        });
    
    // إحصائيات المشاهدات (إذا كان لديك جدول المشاهدات)
    $totalViews = DB::table('course_user')->sum('video_views');
    $totalUniqueViewers = DB::table('course_user')->distinct('user_id')->count('user_id');
    
    return response()->json([
        'success' => true,
        'statistics' => [
            'total_ratings' => $totalRatings,
            'average_rating' => round($averageRating, 1),
            'total_reviews' => $totalReviews,
            'total_views' => $totalViews,
            'total_unique_viewers' => $totalUniqueViewers,
            'rating_distribution' => $ratingDistribution
        ],
        'ratings' => $ratings
    ]);
}
}
