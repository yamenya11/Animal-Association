<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\AnimalCase;
class Appointment extends Model
{
    use HasFactory;

      protected $fillable =  [
    'user_id', 'doctor_id', 'animal_case_id', 
    'scheduled_date', 'scheduled_time', 'description', 'status'
];

     public function user()
    {
        return $this->belongsTo(User::class);
    }


   public function animalCase()
{
    return $this->belongsTo(AnimalCase::class);
}
public function doctor()
{
    return $this->belongsTo(User::class, 'doctor_id');
}

protected $casts = [
    'scheduled_at' => 'datetime',
];



}
