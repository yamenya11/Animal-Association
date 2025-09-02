<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Adoption;
use App\Models\TemporaryCareRequest;
class Animal extends Model
{
    use HasFactory;


    protected $fillable = [
    'user_id',
    'name',
    'type_id',
    'birth_date',
    'health_info',
    'image',
    'is_adopted',
    'breed',
    'adopted_at',
    'available_for_care',
    'purpose',
    'describtion'
];
// في App\Models\Animal
// protected $casts = [
//     'is_adopted' => 'boolean',
//     'available_for_care' => 'boolean'
// ];
 public function scopeAvailableForCare(Builder $query): Builder
    {
        return $query->where('purpose', 'temporary_care')
                   ->where('available_for_care', true)
                   ->where('is_adopted', false);
    }
        public function adoptions()
        {
            return $this->hasMany(Adoption::class);
        }
 
        public function type()
        {
            return $this->belongsTo(AnimalType::class, 'type_id');
        }
        public function temporaryCareRequests()
        {
            return $this->hasMany(TemporaryCareRequest::class);
        }

        public function user()
        {
            return $this->belongsTo(User::class, 'user_id');
        }
        public function reports()
        {
            return $this->hasMany(Report::class);
        }

                public function vaccines()
        {
            return $this->hasMany(Vaccine::class);
        }
}
