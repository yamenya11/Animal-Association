<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Policies\PostPolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class PolicyPostService
{
    protected $postPolicy;

    public function __construct(PostPolicy $postPolicy)
    {
        $this->postPolicy = $postPolicy;
    }

public function createOfficialPost($user, array $data)
{
    $imagePath = isset($data['image']) ? $data['image']->store('official-posts', 'public') : null;

    $post = Post::create([
        'user_id' => $user->id,
        'title' => $data['title'],
        'content' => $data['content'],
        'type_post' => $data['type_post'],
        'image' => $imagePath,
        'status' => 'approved'
    ]);

    // إضافة رابط الصورة للاستجابة
    if ($post->image) {
        $post->image_url = config('app.url') . '/storage/' . $post->image;
    }

    return $post;
}


public function createPost(User $user, array $data): Post
{
    $imagePath = isset($data['image']) ? $data['image']->store('posts', 'public') : null;

    $post = Post::create([
        'user_id' => $user->id,
        'title' => $data['title'],
        'content' => $data['content'],
        'type_post' => $data['type_post'],
        'image' => $imagePath,
        'is_official' => $data['is_official'] ?? false,
        'status' => $user->hasRole(['admin', 'employee']) ? 'approved' : 'pending'
    ]);

    // إضافة رابط الصورة للاستجابة
    if ($post->image) {
        $post->image_url = config('app.url') . '/storage/' . $post->image;
    }

    return $post;
}

public function forceDeletePost(Post $post): bool
{
    if ($post->image) {
        Storage::disk('public')->delete($post->image);
    }
    return $post->forceDelete();
}

  
   public function updatePost(User $user, Post $post, array $data): Post
{
    if (!$this->postPolicy->update($user, $post)) {
        abort(403, 'غير مصرح بالتعديل');
    }

    if (isset($data['image'])) {
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
        $data['image'] = $data['image']->store('posts', 'public');
    }

    $post->update($data);
    
    // إضافة رابط الصورة للاستجابة بعد التحديث
    if ($post->image) {
        $post->image_url = config('app.url') . '/storage/' . $post->image;
    }

    return $post;
}


   public function getUserPosts(User $user): Collection
{
    return $user->posts()->with(['comments'])->latest()->get()->map(function($post) {
        $postData = $post->toArray();
        if ($post->image) {
            $postData['image_url'] = config('app.url') . '/storage/' . $post->image;
        }
        return $postData;
    });
}
}