<?php

namespace App\Services;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Comment;
use App\Models\Like;
use App\Notifications\PostStatusUpdated;
class PostService
{

    public function createPost(Request $request): array
    {
         $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'image'   => 'nullable|image|max:2048', // اختياري
        ]);

      if ($request->hasFile('image')) {
            $file = $request->file('image');
            $file_extension = date('YmdHi') . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/images/photo', $file_extension); 
          
           }
     $data = [
            'user_id' => Auth::id(),
            'title'   => $request->title,
            'content' => $request->content,
            'status'  => 'pending', 
            'image'=>$path
        ];
         $post = Post::create($data);
        return [
            'status'  => true,
            'message' => 'تم إرسال المنشور بانتظار الموافقة.',
            'data'    => $post,
        ];
    }

 
    public function respondToPost($postId, string $action): array
    {
        $post = Post::findOrFail($postId);

    if (!in_array($action, ['approved', 'rejected'])) {
        return [
            'status' => false,
            'message' => 'إجراء غير صالح.',
        ];
    }

    $post->status = $action;
    $post->save();

    // إرسال الإشعار لصاحب المنشور
    $post->user->notify(new PostStatusUpdated($post, $action));

    return [
        'status' => true,
        'message' => $action === 'approved'
            ? 'تمت الموافقة على المنشور.'
            : 'تم رفض المنشور.',
        'data' => $post,
    ];
    }

  public function addComment($postId, $content): array
    {
        $post = Post::where('id', $postId)->where('status', 'approved')->first();

        if (!$post) {
            return [
                'status' => false,
                'message' => 'المنشور غير موجود أو لم يتم الموافقة عليه بعد.'
            ];
        }

        $comment = $post->comments()->create([
            'user_id' => Auth::id(),
            'content' => $content,
        ]);

        return [
            'status' => true,
            'message' => 'تم إضافة التعليق.',
            'data' => $comment,
        ];
    }

    public function deleteComment($commentId): array
{
    $comment = Comment::where('id', $commentId)
        ->where('user_id', Auth::id())
        ->first();

    if (!$comment) {
        return [
            'status' => false,
            'message' => 'لا يمكنك حذف هذا التعليق أو التعليق غير موجود.',
        ];
    }

    $comment->delete();

    return [
        'status' => true,
        'message' => 'تم حذف التعليق بنجاح.',
    ];
}

public function toggleLike($postId): array
{
    $post = Post::where('id', $postId)->where('status', 'approved')->first();

    if (!$post) {
        return [
            'status' => false,
            'message' => 'المنشور غير موجود أو لم تتم الموافقة عليه بعد.',
        ];
    }

    $existingLike = $post->likes()->where('user_id', Auth::id())->first();

    if ($existingLike) {
        $existingLike->delete();
        return [
            'status' => true,
            'message' => 'تم إلغاء الإعجاب بالمنشور.',
        ];
    } else {
        $like = $post->likes()->create([
            'user_id' => Auth::id()
        ]);
        return [
            'status' => true,
            'message' => 'تم تسجيل الإعجاب.',
            'data' => $like
        ];
    }
}
   

}