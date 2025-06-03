<?php
namespace App\Services;
use App\Models\Animal;


class AnimalService
{
    public function getAvailableAnimals()
    {
        return Animal::where('is_adopted', false)->get();
    }
}