<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Appointment;

use Illuminate\Database\Eloquent\Relations\HasMany;
class Ambulance extends Model
{
    use HasFactory;
    protected $fillable = [
     
        'driver_name',
        'driver_phone',
        'status'
    ];
       public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
