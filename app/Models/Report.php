<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Report extends Model
{
    use HasFactory;
     protected $fillable = [
        'animal_name',
        'animal_age',
        'animal_weight',
        'image',
        'status',
        'temperature',
        'pluse',
        'respiration',
        'general_condition',
        'midical_separated',
        'note',
        'doctor_id',
        'animal_id'
    ];

        public function getImageUrlAttribute()
        {
        return $this->image ? Storage::url($this->image) : null;
        }

        public function doctor(): BelongsTo
        {
            // افترض أن لديك حقل doctor_id في جدول reports
        return $this->belongsTo(User::class, 'doctor_id');
        }
        public function animal()
            {
                return $this->belongsTo(Animal::class);
            }


}
