<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',        // اسم الخدمة
        'description', // وصف الخدمة
        'price',       // سعر الخدمة
        'duration'     // مدة الخدمة
    ];

    // العلاقة مع المواعيد
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
} 