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
    $request->validate([
        'title'   => 'required|string|max:255',
        'content' => 'required|string',
        'image'   => 'nullable|image|max:2048',
    ]);

    $path = null;
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $fileName = date('YmdHi') . '.' . $file->getClientOriginalExtension();
        // تخزين الملف داخل storage/app/public/images/photo
        $path = $file->storeAs('images/photo', $fileName, 'public'); 
    }

    $data = [
        'user_id' => Auth::id(),
        'title'   => $request->title,
        'content' => $request->content,
        'status'  => 'pending',
        'image'   => $path, // تخزين المسار النسبي فقط
    ];

    $post = Post::create($data);

    // إضافة رابط الصورة مباشرة عند الإرجاع
    $post->image_url = $post->image ? asset('storage/' . $post->image) : null;

    return [
        'status'  => true,
        'message' => 'تم إرسال المنشور بانتظار الموافقة.',
        'data'    => $post,
    ];
}


    function show_post(){
         return Post::join('users', 'posts.user_id', '=', 'users.id')
->select('posts.id as post_id', 'posts.content', 'posts.title', 'posts.image', 'users.id as user_id', 'users.name', 'users.email', 'posts.status')
->where('posts.status', '!=', 'pending') // هنا نمنع إظهار pending
        ->get();

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

    return [
        'status' => true,
        'message' => $action === 'approved'
            ? 'تمت الموافقة على المنشور.'
            : 'تم رفض المنشور.',
        'data' => $post,
    ];
}



//   public function addComment($postId, $content): array
//     {
//         $post = Post::where('id', $postId)->where('status', 'approved')->first();

//         if (!$post) {
//             return [
//                 'status' => false,
//                 'message' => 'المنشور غير موجود أو لم يتم الموافقة عليه بعد.'
//             ];
//         }

//         $comment = $post->comments()->create([
//             'user_id' => Auth::id(),
//             'content' => $content,
//         ]);

//         return [
//             'status' => true,
//             'message' => 'تم إضافة التعليق.',
//             'data' => $comment,
//         ];
//     }
//         public function replay_comment($commentId, $content): array
//         {
//             $comment = Comment::find($commentId);

//             if (!$comment) {
//                 return [
//                     'status' => false,
//                     'message' => 'التعليق غير موجود.'
//                 ];
//             }

//             $reply = new Comment();
//             $reply->user_id = Auth::id();
//             $reply->content = $content;
//             $reply->parent_id = $comment->id; // تأكد أن جدول comments فيه حقل parent_id
//             $reply->post_id = $comment->post_id; // حتى يرتبط بنفس المنشور
//             $reply->save();

//             return [
//                 'status' => true,
//                 'message' => 'تم إضافة الرد بنجاح.',
//                 'data' => $reply,
//             ];
//         }
//             public function deleteComment($commentId): array
//         {
//             $comment = Comment::where('id', $commentId)
//                 ->where('user_id', Auth::id())
//                 ->first();

//             if (!$comment) {
//                 return [
//                     'status' => false,
//                     'message' => 'لا يمكنك حذف هذا التعليق أو التعليق غير موجود.',
//                 ];
//             }

//             $comment->delete();

//             return [
//                 'status' => true,
//                 'message' => 'تم حذف التعليق بنجاح.',
//             ];
//         }

//         public function toggleLike($postId): array
//         {
//             $post = Post::where('id', $postId)->where('status', 'approved')->first();

//             if (!$post) {
//                 return [
//                     'status' => false,
//                     'message' => 'المنشور غير موجود أو لم تتم الموافقة عليه بعد.',
//                 ];
//             }

//             $existingLike = $post->likes()->where('user_id', Auth::id())->first();

//             if ($existingLike) {
//                 $existingLike->delete();
//                 return [
//                     'status' => true,
//                     'message' => 'تم إلغاء الإعجاب بالمنشور.',
//                 ];
//             } else {
//                 $like = $post->likes()->create([
//                     'user_id' => Auth::id()
//                 ]);
//                 return [
//                     'status' => true,
//                     'message' => 'تم تسجيل الإعجاب.',
//                     'data' => $like
//                 ];
//             }
//         }
   

}