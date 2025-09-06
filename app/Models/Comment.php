<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Eloquent\SoftDeletes;
class Comment extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'post_id', 'content', 'parent_id'];


      public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function replies()
     {
    return $this->hasMany(Comment::class, 'parent_id');
     }

    public function parent()
    {
    return $this->belongsTo(Comment::class, 'parent_id');
    }
        protected static function booted()
    {
        static::deleting(function ($comment) {
            $comment->replies()->delete();
        });
    }


}
