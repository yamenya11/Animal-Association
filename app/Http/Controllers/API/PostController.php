<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PostService;
use Illuminate\Support\Facades\Auth;
class PostController extends Controller
{
    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }
    public function store(Request $request)
    {
        $response = $this->postService->createPost($request);
        return response()->json($response, $response['status'] ? 201 : 400);
    }
 public function show_all_post(){
    $userId = Auth::id();
    
    $posts = $this->postService->show_post();

    // إضافة image_url لكل منشور
    $postsWithImageUrl = $posts->map(function($post) {
        $postData = (array)$post;
        if (!empty($postData['image'])) {
            $postData['image_url'] = config('app.url') . '/storage/' . $postData['image'];
        } else {
            $postData['image_url'] = null;
        }
        return $postData;
    });

    return response()->json([
        'user_id' => $userId,
        'status' => true,
        'data' => $postsWithImageUrl,
    ]);
}
}
