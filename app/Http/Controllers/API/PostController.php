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
     
      
        $post = $this->postService->show_post();

        return response()->json([
            'user_id'=>$userId,
            'status' => true,
            'data' => $post,
        ]);
    
    }
}
