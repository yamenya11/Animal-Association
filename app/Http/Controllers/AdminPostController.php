<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PostService;
use App\Models\Post;
class AdminPostController extends Controller
{
    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

      public function respond(Request $request, $postId)
    {
        $request->validate([
            'action' => 'required|in:approved,rejected',
        ]);

        $result = $this->postService->respondToPost($postId, $request->action);

        return response()->json($result);
    }


   public function index()
{
    // جلب كل المنشورات التي لم تتم الموافقة عليها بعد
    $pendingPosts = Post::where('status', 'pending')->with('user')->latest()->get();

     $posts = Post::join('users', 'posts.user_id', '=', 'users.id')
        ->where('posts.status', 'pending')
        ->select(
            'posts.id',
            'posts.title',
            'posts.content',
            'posts.status',
            'posts.image',
            'posts.created_at',
            'users.id as user_id',
            'users.name as user_name',
            'users.email as user_email'
        )
        ->orderBy('posts.created_at', 'desc')
        ->get();

    return response()->json([
        'status' => true,
        'data' => $posts
    ]);
} 


}
