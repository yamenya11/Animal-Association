<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use App\Services\PostService;
class CommentController extends Controller
{
     protected $postService;

        public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

      public function store(Request $request, $postId)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $response = $this->postService->addComment($postId, $request->content);
        return response()->json($response);
    }
    public function destroy($commentId)
{
    $response = $this->postService->deleteComment($commentId);
    return response()->json($response);
}
}
