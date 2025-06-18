<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use App\Services\LikeService;
class LikeController extends Controller
{
    protected $likeservice;

    public function __construct(LikeService $likeservice)
    {
        $this->likeservice = $likeservice;
    }

 public function toggle($postId)
{
    $response = $this->likeservice->toggleLike($postId);
    return response()->json($response);
}

}
