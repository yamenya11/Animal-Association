<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Adoption;
class Animal extends Model
{
    use HasFactory;


    protected $fillable = [
    'name',
    'type',
    'age',
    'health_info',
    'image',
    'is_adopted',
];

public function adoptions()
{
    return $this->hasMany(Adoption::class);
}
}
