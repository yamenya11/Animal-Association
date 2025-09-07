<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimalGuide extends Model
{
    use HasFactory;

protected $fillable = [
        'name', 'type', 'image', 'description', 'food','category_id','user_id'
    ];

    public function category()
{
    return $this->belongsTo(Category::class,'category_id');
}
 
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
