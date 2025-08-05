<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Animal;
use App\Models\Appointment;
use App\Models\User;


class AnimalCase extends Model
{
    use HasFactory;
   protected $fillable = [
        'name_animal',
        'case_type',
        'description',
        'image',
        'emergency_address',
        'emergency_phone',
        'user_id',
        'request_type'
        ,
        'approval_status',
           'doctor_id'
    ];


    protected $casts = [
    'birth_date' => 'date',
    'is_adopted' => 'boolean'
];
     public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
     public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

   public function user()
{
    return $this->belongsTo(User::class);
}

public function doctor()
{
    return $this->belongsTo(User::class, 'doctor_id');
}
}
