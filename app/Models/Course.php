<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'video', 'category_id', 'doctor_id','duration'];
  public function category()
{
    return $this->belongsTo(CatgoryCourse::class, 'category_id');
}

public function doctor()
{
    return $this->belongsTo(User::class, 'doctor_id');
}

    public function getFormattedDurationAttribute()
{
    return "مدة الكورس: " . $this->duration;
}
public function getVideoUrlAttribute()
{
    if (empty($this->video)) {
        return null;
    }
    
    return Storage::disk('public')->url($this->video);
}
}
