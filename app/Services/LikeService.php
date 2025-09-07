<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class LikeService
{
    public function toggleLike($postId): array
    {
        $post = Post::where('id', $postId)->where('status', 'approved')->first();

        if (!$post) {
            return ['status' => false, 'message' => 'المنشور غير موجود أو لم تتم الموافقة عليه بعد.'];
        }

        $existingLike = $post->likes()->where('user_id', Auth::id())->first();

        if ($existingLike) {
            $existingLike->delete();
            return ['status' => true, 'message' => 'تم إلغاء الإعجاب بالمنشور.'];
        } else {
            $like = $post->likes()->create(['user_id' => Auth::id()]);
            return ['status' => true, 'message' => 'تم تسجيل الإعجاب.', 'data' => $like];
        }
    }

public function getLikesCount($postId): int
    {
        $post = Post::findOrFail($postId);
        return $post->likes()->count();
    }

  
    public function getAllLike(): array
    {
        return Post::withCount('likes')
            ->get()
            ->map(function ($post) {
                return [
                    'post_id' => $post->id,
                    'title' => $post->title,
                    'likes_count' => $post->likes_count
                ];
            })
            ->toArray();
    }
    
}
