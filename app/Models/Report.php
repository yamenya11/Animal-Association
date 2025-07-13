<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class Report extends Model
{
    use HasFactory;
     protected $fillable = [
        'animal_name',
        'animal_age',
        'animal_weight',
        'image',
        'status',
        'temperature',
        'pluse',
        'respiration',
        'general _condition',
        'midical_separated',
        'note'
    ];

      public function getImageUrlAttribute()
    {
        return $this->image ? Storage::url($this->image) : null;
    }
}
