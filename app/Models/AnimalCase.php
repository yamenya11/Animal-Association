<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Animal;
use App\Models\Appointment;
class AnimalCase extends Model
{
    use HasFactory;
     protected $fillable = [
        'name_animal',
        'case_type',
        'description',
        'image',
        'user_id'
    ];

     public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
     public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

   public function user()
{
    return $this->belongsTo(User::class);
}
}
