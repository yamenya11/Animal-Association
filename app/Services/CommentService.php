<?php
namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class CommentService
{
    public function addComment($postId, $content): array
    {
        $post = Post::where('id', $postId)->where('status', 'approved')->first();

        if (!$post) {
            return ['status' => false, 'message' => 'المنشور غير موجود أو لم يتم الموافقة عليه بعد.'];
        }

        $comment = $post->comments()->create([
            'user_id' => Auth::id(),
            'content' => $content,
        ]);

        return ['status' => true, 'message' => 'تم إضافة التعليق.', 'data' => $comment];
    }

    public function replyComment($commentId, $content): array
    {
        $comment = Comment::find($commentId);

        if (!$comment) {
            return ['status' => false, 'message' => 'التعليق غير موجود.'];
        }

        $reply = new Comment();
        $reply->user_id = Auth::id();
        $reply->content = $content;
        $reply->parent_id = $comment->id;
        $reply->post_id = $comment->post_id;
        $reply->save();

        return ['status' => true, 'message' => 'تم إضافة الرد بنجاح.', 'data' => $reply];
    }

    public function deleteComment($commentId): array
    {
        $comment = Comment::where('id', $commentId)->where('user_id', Auth::id())->first();

        if (!$comment) {
            return ['status' => false, 'message' => 'لا يمكنك حذف هذا التعليق أو التعليق غير موجود.'];
        }

        $comment->delete();

        return ['status' => true, 'message' => 'تم حذف التعليق بنجاح.'];
    }
}
