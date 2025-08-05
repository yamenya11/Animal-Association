<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\VolunteerRequest;
class VolunteerType extends Model
{
    use HasFactory;
     protected $fillable = ['name_en', 'slug', 'description', 'is_active'];

public function volunteerRequests()
{
    return $this->hasMany(VolunteerRequest::class, 'volunteer_type_id');
}

    // دالة مساعدة للحصول على عدد المتطوعين
    public function getVolunteersCountAttribute()
    {
        return $this->volunteerRequests()->count();
    }

    // دالة لتحديد إذا كان القسم نشطاً
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
