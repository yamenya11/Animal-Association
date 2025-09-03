<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaccine extends Model
{
    use HasFactory;
      protected $fillable = [
    
    'animal_id', 
    'gender',
    'type',
    'image',
    'due_date'
];

public function dueToday()
    {
        return $this->whereDate('due_date', now()->toDateString())
                    ->with('animal') 
                    ->get();
    }
public function animal()
{
    return $this->belongsTo(Animal::class);
}
}
