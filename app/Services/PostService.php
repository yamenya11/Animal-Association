<?php

namespace App\Services;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class PostService
{

    public function createPost(Request $request): array
    {
         $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'image'   => 'nullable|image|max:2048', // اختياري
        ]);

           $data = [
            'user_id' => Auth::id(),
            'title'   => $request->title,
            'content' => $request->content,
            'status'  => 'pending', // بانتظار موافقة المدير
        ];

         $post = Post::create($data);

        return [
            'status'  => true,
            'message' => 'تم إرسال المنشور بانتظار الموافقة.',
            'data'    => $post,
        ];
    }

}