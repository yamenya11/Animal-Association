<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaccine extends Model
{
    use HasFactory;
       protected $fillable = [
        'animal_name', 'type', 'due_date',
    ];

    public function dueToday()
{
    return Vaccine::whereDate('due_date', now()->toDateString())->get();
}
}
