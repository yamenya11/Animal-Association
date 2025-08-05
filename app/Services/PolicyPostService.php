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

    return Post::create([
        'user_id' => $user->id,
        'title' => $data['title'],
        'content' => $data['content'],
        'type_post' => $data['type_post'],
        'image' => $imagePath,
        'status' => 'approved'
    ]);
}
  public function createPost(User $user, array $data): Post
{
    $imagePath = isset($data['image']) ? $data['image']->store('posts', 'public') : null;

    return Post::create([
        'user_id' => $user->id,
        'title' => $data['title'],
        'content' => $data['content'],
        'type_post' => $data['type_post'],
        'image' => $imagePath,
        'is_official' => $data['is_official'] ?? false,
        'status' => $user->hasRole(['admin', 'employee']) ? 'approved' : 'pending'
    ]);
}

public function forceDeletePost(Post $post): bool
{
    if ($post->image) {
        Storage::disk('public')->delete($post->image);
    }
    return $post->forceDelete();
}

    /**
     * تحديث بوست موجود
     */
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
        return $post;
    }


    public function getUserPosts(User $user): Collection
    {
        return $user->posts()->with(['comments'])->latest()->get();
    }
}