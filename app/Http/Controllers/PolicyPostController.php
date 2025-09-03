<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;
use App\Services\PolicyPostService;
class PolicyPostController extends Controller
{
    protected $postService;

    public function __construct(PolicyPostService $postService)
    {
        $this->postService = $postService;
    }
public function storeOfficial(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'type_post' => 'required|in:adoption,opinion,temporary_care',
        'image' => 'nullable|image|max:2048'
    ]);

    // أرسل الملف نفسه إلى الخدمة بدون تحويله مسبقًا
    $post = $this->postService->createOfficialPost(
        $request->user(), 
        array_merge($validated, ['image' => $request->file('image') ?? null])
    );

    return response()->json([
        'status' => true,
        'message' => 'تم إنشاء المنشور الرسمي بنجاح',
        'data' => $post
    ], 201);
}

    // إنشاء بوست
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type_post' => 'required|in:adoption,opinion,temporary_care,announcement,news',
            'image' => 'nullable|image|max:2048',
            'is_official' => 'nullable|boolean' // للإدمن والموظف فقط
        ]);

        // تحديد إذا كان المنشور رسمي (للأدمن/الموظف)
        $isOfficial = $request->user()->hasRole(['admin', 'employee']) 
                    && $request->input('is_official', false);

        $post = $this->postService->createPost($request->user(), array_merge($validated, [
            'is_official' => $isOfficial
        ]));

        return response()->json([
            'status' => true,
            'message' => $isOfficial ? 'تم إنشاء المنشور الرسمي' : 'تم إنشاء المنشور',
            'data' => $post
        ], 201);
    }

    // حذف بوست
    public function destroy(Request $request, Post $post)
    {
        $this->postService->deletePost($request->user(), $post);
        return response()->noContent();
    }

    // حذف نهائي (للأدمن فقط)
  public function forceDestroy(Post $post)
{
    $this->postService->forceDeletePost($post);
    
    return response()->json([
        'status' => true,
        'message' => 'تم حذف المنشور بنجاح'
    ]);
}


public function getOfficialPosts()
{
    // جلب كل المنشورات الرسمية (approved) للمسؤولين
    $posts = \App\Models\Post::where('type_post', '!=', null)
        ->where('status', 'approved')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($post) {
            return [
                'id' => $post->id,
                'user_id' => $post->user_id,
                'title' => $post->title,
                'content' => $post->content,
                'type_post' => $post->type_post,
                'image_url' => $post->image ? config('app.url') . '/storage/' . $post->image : null,
                'status' => $post->status,
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
            ];
        });

    return response()->json([
        'status' => true,
        'data' => $posts
    ]);
}

}