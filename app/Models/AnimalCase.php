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
        'animal_id',
        'case_type',
        'description',
        'image',
    ];

     public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
     public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
