<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use App\Services\LikeService;
use Illuminate\Http\JsonResponse;
class LikeController extends Controller
{
    protected $likeservice;

    public function __construct(LikeService $likeservice)
    {
        $this->likeservice = $likeservice;
    }

 public function toggle($postId)
{
    $response = $this->likeservice->toggleLike($postId);
    return response()->json($response);
}
 public function likesCount($postId):  JsonResponse
    {
        try {
            $count = $this->likeservice->getLikesCount($postId);
            
            return response()->json([
                'success' => true,
                'count' => $count,
                'message' => 'تم جلب عدد الإعجابات بنجاح'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب عدد الإعجابات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على جميع الإعجابات مع عددها لكل منشور
     */
    public function getAllLike(): JsonResponse
    {
        try {
            $postsWithLikes = $this->likeService->getAllLike();
            
            return response()->json([
                'success' => true,
                'data' => $postsWithLikes,
                'message' => 'تم جلب جميع الإعجابات بنجاح'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الإعجابات',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

