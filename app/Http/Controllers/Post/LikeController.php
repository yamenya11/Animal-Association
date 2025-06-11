<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use App\Services\PostService;
class LikeController extends Controller
{
    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

 public function toggle($postId)
{
    $response = $this->postService->toggleLike($postId);
    return response()->json($response);
}

}
