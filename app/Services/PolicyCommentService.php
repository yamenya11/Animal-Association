<?php
namespace App\Services;

use App\Models\Comment;
use App\Models\User;
use App\Policies\CommentPolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PolicyCommentService
{
    protected $commentPolicy;

    public function __construct(CommentPolicy $commentPolicy)
    {
        $this->commentPolicy = $commentPolicy;
    }

    /**
     * إضافة تعليق جديد
     */
    public function createComment(User $user, array $data): Comment
    {
        return Comment::create([
            'user_id' => $user->id,
            'post_id' => $data['post_id'],
            'content' => $data['content'],
            'parent_id' => $data['parent_id'] ?? null
        ]);
    }

    /**
     * تحديث تعليق
     */
    public function updateComment(User $user, Comment $comment, string $content): Comment
    {
        if (!$this->commentPolicy->edit($user, $comment)) {
            abort(403, 'غير مصرح بالتعديل');
        }

        $comment->update(['content' => $content]);
        return $comment;
    }

  
    public function getPostComments(int $postId): Collection
    {
        return Comment::with('user')
            ->where('post_id', $postId)
            ->whereNull('parent_id')
            ->latest()
            ->get();
    }

    /**
     * الحصول على ردود تعليق معين
     */
    public function getCommentReplies(int $commentId): Collection
    {
        return Comment::with('user')
            ->where('parent_id', $commentId)
            ->latest()
            ->get();
    }
// app/Services/CommentService.php

public function forceDeleteComment(User $user, Comment $comment): array
{
    $counts = [
        'replies' => $comment->replies()->count(),
     
    ];

    DB::transaction(function () use ($comment) {
        $comment->replies()->forceDelete();
    
        $comment->forceDelete();
    });

    return $counts;
}

public function replyToComment(User $user, Comment $comment, string $content): Comment
{
    return Comment::create([
        'user_id'   => $user->id,
        'post_id'   => $comment->post_id,
        'content'   => $content,
        'parent_id' => $comment->id
    ]);
}

}