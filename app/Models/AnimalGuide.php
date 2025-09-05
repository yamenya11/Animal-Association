<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimalGuide extends Model
{
    use HasFactory;


    public function category()
{
    return $this->belongsTo(Category::class,'category_id');
}
 protected $fillable = [
        'name', 'type', 'image', 'description', 'food','category_id'
    ];
}
