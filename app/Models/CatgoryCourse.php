<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatgoryCourse extends Model
{

    use HasFactory;
     protected $table = 'catgory_courses';
    protected $fillable = ['name'];

     public function courses()
    {
        return $this->hasMany(Course::class, 'category_id');
    }
}
