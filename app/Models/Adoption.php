<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Animal;
class Adoption extends Model
{
    use HasFactory;


   protected $fillable = [
    'user_id', 
    'animal_id', 
    'address', 
    'birth_date', 
    'type_id', 
    'phone', 
    'status',
];

     public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
    public function animalType()
    {
        return $this->belongsTo(AnimalType::class, 'type_id');
    }
}
