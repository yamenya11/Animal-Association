<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PostService;
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
}
