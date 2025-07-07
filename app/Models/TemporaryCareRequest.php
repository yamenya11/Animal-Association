<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryCareRequest extends Model
{
    use HasFactory;
  protected $fillable = [
    'user_id',
    'animal_id',
    'address',
    'duration',
    'custom_duration',
    'health_info',
    'vet_id',
    'status',
    'type'
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // علاقة مع الحيوان
    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

}
