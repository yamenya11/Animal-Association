<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\AnimalCase;
use App\Models\Ambulance;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Appointment extends Model
{
    use HasFactory;

      protected $fillable =  [
    'user_id', 'doctor_id', 'animal_case_id', 
    'scheduled_date', 'scheduled_time', 'description', 'status','is_immediate','employee_id','ambulance_id'
];

     public function user()
    {
        return $this->belongsTo(User::class);
    }


   public function animalCase()
{
    return $this->belongsTo(AnimalCase::class);
}
public function doctor()
{
    return $this->belongsTo(User::class, 'doctor_id');
}
public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
protected $casts = [
    'scheduled_at' => 'datetime',
];

  public function ambulance(): BelongsTo
    {
        return $this->belongsTo(Ambulance::class);
    }
}
