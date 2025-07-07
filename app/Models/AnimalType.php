<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Animal;
class AnimalType extends Model
{
    use HasFactory;
       protected $fillable = ['name'];

    public static function firstOrCreateByName($name)
    {
        return static::firstOrCreate(['name' => $name]);
    }
     public function animals()
    {
        return $this->hasMany(Animal::class);
    }
}
