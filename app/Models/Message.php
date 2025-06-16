<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_user_id', // معرف المستخدم المرسل
        'to_user_id',   // معرف المستخدم المستقبل
        'message'       // محتوى الرسالة
    ];

    // العلاقة مع المستخدم المرسل
    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    // العلاقة مع المستخدم المستقبل
    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
} 