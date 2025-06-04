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

      if ($request->hasFile('image')) {
            $file = $request->file('image');
            $file_extension = date('YmdHi') . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/images/photo', $file_extension); 
          
           }
     $data = [
            'user_id' => Auth::id(),
            'title'   => $request->title,
            'content' => $request->content,
            'status'  => 'pending', 
            'image'=>$path
        ];
         $post = Post::create($data);
        return [
            'status'  => true,
            'message' => 'تم إرسال المنشور بانتظار الموافقة.',
            'data'    => $post,
        ];
    }

}