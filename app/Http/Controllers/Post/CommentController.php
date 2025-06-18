<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use App\Services\CommentService;
use App\Models\Comment; 
use App\Services\PostService;
class CommentController extends Controller
{
            protected $commentService;

        public function __construct(CommentService $commentService)
        {
            $this->commentService = $commentService;
        }

        public function store(Request $request, $postId)
        {
            $request->validate([
                'content' => 'required|string',
            ]);

            $response = $this->commentService->addComment($postId, $request->content);
            return response()->json($response);
        }
            public function destroy($commentId)
        {
            $response = $this->commentService->deleteComment($commentId);
            return response()->json($response);
        } 
        public function reply(Request $request, $commentId)
        {
            $request->validate([
                'content' => 'required|string',
            ]);

            $response = $this->commentService->replyComment($commentId, $request->content);
            return response()->json($response);
        }
   public function index($postId)
{
    $comments = Comment::leftJoin('users', 'comments.user_id', '=', 'users.id')
        ->select(
            'comments.id',
            'comments.content',
            'comments.parent_id',
            'comments.post_id',
            'comments.created_at',
            'users.name as user_name'
        )
        ->where('comments.post_id', $postId)
        ->whereNull('comments.parent_id')
        ->get();

    foreach ($comments as $comment) {
        $replies = Comment::leftJoin('users', 'comments.user_id', '=', 'users.id')
            ->select(
                'comments.id',
                'comments.content',
                'comments.parent_id',
                'comments.post_id',
                'comments.created_at',
                'users.name as user_name'
            )
            ->where('comments.parent_id', $comment->id)
            ->get();

        $comment->replies = $replies;
    }

    return response()->json([
        'status' => true,
        'data' => $comments,
    ]);
}}
