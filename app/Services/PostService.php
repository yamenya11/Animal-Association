<?php

namespace App\Services;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Comment;
use App\Models\Like;
use App\Notifications\PostStatusUpdated;
use App\Services\NotificationService;
class PostService
{

public function createPost(Request $request): array
{
    $validated = $request->validate([
        'type_post' => 'required|in:adoption,opinion,temporary_care',
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'image' => 'nullable|image|max:2048',
    ]);

    $path = null;
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('images/photocaseanimal', 'public');
    }

    $post = Post::create([
        'user_id' => Auth::id(),
        'title' => $validated['title'],
        'content' => $validated['content'],
        'type_post' => $validated['type_post'],
        'status' => 'pending',
        'image' => $path,
    ]);

    // إضافة رابط الصورة للاستجابة
    $responseData = $post->toArray();
    if ($post->image) {
        $responseData['image_url'] = config('app.url') . '/storage/' . $post->image;
    }

    return [
        'status' => true,
        'message' => 'تم إرسال المنشور بانتظار الموافقة.',
        'data' => $responseData,
    ];
}

public function show_post()
{
    $posts = Post::join('users', 'posts.user_id', '=', 'users.id')
        ->select(
            'posts.id as post_id',
            'posts.content',
            'posts.title',
            'posts.type_post',
            'posts.image',
            'users.id as user_id',
            'users.name',
            'users.email',
            'posts.status'
        )
        ->where('posts.status', '!=', 'pending')
        ->get();

    // معالجة كل منشور على حدة
    return $posts->map(function($post) {
        // إذا كان $post هو JSON string، قم بتحويله إلى array
        if (is_string($post)) {
            $postData = json_decode($post, true);
        } else {
            $postData = $post->toArray();
        }

        if (!empty($postData['image'])) {
            $postData['image_url'] = config('app.url') . '/storage/' . $postData['image'];
        } else {
            $postData['image_url'] = null;
        }
        
        return $postData;
    });
}
  public function respondToPost($postId, string $action): array
{
    $post = Post::with('user')->findOrFail($postId);

    if (!in_array($action, ['approved', 'rejected'])) {
        return [
            'status' => false,
            'message' => 'إجراء غير صالح.',
        ];
    }

    $post->status = $action;
    $post->save();

    // إشعار عبر خدمة الإشعارات
    $notificationService = app(NotificationService::class);
    $notificationService->sendPostStatusNotification($post, $action);
  $postData = $post->toArray();
    if ($post->image) {
        $postData['image_url'] = config('app.url') . '/storage/' . $post->image;
    }
     return [
        'status' => true,
        'message' => $action === 'approved'
            ? 'تمت الموافقة على المنشور.'
            : 'تم رفض المنشور.',
        'data' => $postData,
    ];
}
}