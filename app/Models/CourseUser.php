<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseUser extends Model
{
    use HasFactory;
    protected $table = 'course_user';

            protected $fillable = [
                'course_id',
                'user_id',
                'is_liked',
                'video_views',
                'last_watched_at', 
            ];

    
        protected $casts = [
                'is_liked' => 'boolean',
                'last_watched_at' => 'datetime',
            ];

            // دالة لتسجيل مشاهدة جديدة
            public function addView()
            {
                $this->video_views++;
                $this->last_watched_at = now();
                $this->save();
            }

            // دالة لتبديل حالة الإعجاب
            public function toggleLike()
            {
                $this->is_liked = !$this->is_liked;
                $this->save();
                
                return $this->is_liked;
            }

            // دالة للتحقق إذا شاهد المستخدم الكورس
            public function hasWatched()
            {
                return $this->video_views > 0;
            }
}
