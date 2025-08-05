<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Services\PolicyCommentService;
use Illuminate\Http\Request;
use App\Models\User; 
class PolicyCommentController extends Controller
{
    protected $commentService;

    public function __construct(PolicyCommentService $commentService)
    {
        $this->commentService = $commentService;
    }



   
    // إنشاء تعليق
public function store(Request $request)
{
    // 1. التحقق من صحة البيانات
    $validated = $request->validate([
        'post_id' => 'required|exists:posts,id',
        'content' => 'required|string',
        'parent_id' => 'nullable|exists:comments,id' // أعدت إضافتها لأنها ضرورية
    ]);

    // 2. إنشاء التعليق باستخدام الخدمة
    $comment = $this->commentService->createComment(
        $request->user(),
        $validated // نمرر جميع البيانات المصدق عليها
    );

    // 3. إرجاع الرد مع التعليق المنشأ
    return response()->json([
        'status' => true,
        'message' => isset($validated['parent_id']) ? 'تم إضافة الرد بنجاح' : 'تم إضافة التعليق بنجاح',
        'data' => $comment // الآن المتغير معرّف مسبقاً
    ], 201);
}
    // حذف تعليق
    public function destroy(Request $request, Comment $comment)
    {
        $this->commentService->deleteComment($request->user(), $comment);
        return response()->noContent();
    }
// app/Http/Controllers/PolicyCommentController.php

public function forceDestroy(Request $request, Comment $comment)
{
    try {
        $this->authorize('forceDelete', $comment);
        
        // جمع البيانات قبل الحذف
        $deletedData = [
            'id' => $comment->id,
          //  'content_snippet' => Str::limit($comment->content, 30),
            'author' => $comment->user->name,
            'created_at' => $comment->created_at->toDateTimeString()
        ];

        $affectedCounts = $this->commentService->forceDeleteComment($request->user(), $comment);
        
        return response()->json([
            'status' => true,
            'message' => 'تم الحذف النهائي بنجاح',
            'data' => [
                'deleted_comment' => $deletedData,
                'statistics' => [
                    'replies_deleted' => $affectedCounts['replies'],
                ],
                'performed_by' => $request->user()->name,
                'timestamp' => now()->toDateTimeString()
            ]
        ]);

    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        return response()->json([
            'status' => false,
            'message' => 'خطأ في الصلاحيات: ' . $e->getMessage(),
            'data' => null
        ], 403);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'فشل في العملية: ' . $e->getMessage(),
            'data' => null
        ], 500);
    }
}

public function reply(Request $request, Comment $comment)
{
    $request->validate([
        'content' => 'required|string|max:1000',
    ]);

    $reply = app(\App\Services\PolicyCommentService::class)
        ->replyToComment($request->user(), $comment, $request->content);

    return response()->json([
        'status' => true,
        'message' => 'تم إضافة الرد بنجاح',
        'data' => [
            'reply_id' => $reply->id,
            'content' => $reply->content,
            'created_at' => $reply->created_at->toDateTimeString(),
            'author' => $reply->user->name,
        ]
    ]);
}
public function getCommentsWithReplies($postId)
{
    $comments = Comment::with(['user', 'replies.user'])
        ->where('post_id', $postId)
        ->whereNull('parent_id')
        ->latest()
        ->get()
        ->map(function ($comment) {
            return [
                'id' => $comment->id,
                'author' => $comment->user->name,
                'content' => $comment->content,
                'created_at' => $comment->created_at->format('Y-m-d H:i'),
                'replies' => $comment->replies->map(function ($reply) {
                    return [
                        'id' => $reply->id,
                        'author' => $reply->user->name,
                        'content' => $reply->content,
                        'created_at' => $reply->created_at->format('Y-m-d H:i'),
                    ];
                })
            ];
        });

    return response()->json([
        'status' => true,
        'comments' => $comments
    ]);
}


}