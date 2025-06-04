<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\AnimalCase;
class Appointment extends Model
{
    use HasFactory;

      protected $fillable = [
        'user_id',
        'animal_case_id',
        'scheduled_at',
        'status',
    ];

     public function user()
    {
        return $this->belongsTo(User::class);
    }

   public function animalCase()
{
    return $this->belongsTo(AnimalCase::class);
}
}
