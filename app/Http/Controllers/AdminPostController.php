<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PostService;
use App\Services\AdoptionService;
use App\Models\Post;
use App\Models\User;
class AdminPostController extends Controller
{
    protected $postService;
    protected $Adopt;
   public function __construct(PostService $postService, AdoptionService $Adopt)
{
    $this->postService = $postService;
    $this->Adopt = $Adopt;
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

    public function show_post_admin()
  {
    // جلب كل المنشورات التي لم تتم الموافقة عليها بعد
    $pendingPosts = Post::where('status', 'approved')->with('user')->latest()->get();

     $posts = Post::join('users', 'posts.user_id', '=', 'users.id')
        ->where('posts.status', 'approved')
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

 // للإدمن: الموافقة / الرفض
    public function respond_Adopt(Request $request, $id)
    {
       
      // $user=User::Auth();
        $request->validate([
           // 'user_id'=>$user,
            'status' => 'required|in:approved,rejected',
            
        ]);
   
        $resp= $this->Adopt->accept_Adopt_Admin(
            $id,
            $request->status,
         
        );
    //    $result = $this->Adopt->accept_Adopt_Admin($resp);
        return response()->json($resp);
    }

    public function getAllAdoptionRequests()
{
    $adoptions = $this->Adopt->getAllAdoptions();

    return response()->json([
        'status' => true,
        'data' => $adoptions
    ]);
}

}
