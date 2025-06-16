<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\AnimalCase;
class Appointment extends Model
{
    use HasFactory;

      protected $fillable = [
        'user_id',
        'animal_case_id',
        'scheduled_at',
        'status',
        'employee_id'
    ];

     public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
{
    return $this->belongsTo(User::class, 'employee_id');
}
   public function animalCase()
{
    return $this->belongsTo(AnimalCase::class);
}
}
