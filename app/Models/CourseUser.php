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
        'views',
    ];

    // مثال: دالة لتسجيل مشاهدة جديدة
    public function addView()
    {
        $this->increment('views');
    }

    // مثال: دالة لتبديل حالة الإعجاب
    public function toggleLike()
    {
        $this->update(['is_liked' => !$this->is_liked]);
    }
}
