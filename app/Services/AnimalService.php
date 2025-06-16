<?php
namespace App\Services;
use App\Models\Animal;


class AnimalService
{
    public function getAvailableAnimals()
    {
        return Animal::select('animals.id',
        'animals.name',
        'animals.type',
        'animals.age',
        'animals.health_info',
        'animals.image',
        'animals.is_adopted')
       -> where('is_adopted', false)->get();
    }
}
   