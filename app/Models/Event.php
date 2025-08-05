<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'location',
        'max_participants',
        'status',
        'created_by',
        'views'
    ];

    protected $casts = [
    'start_date' => 'datetime',
    'end_date' => 'datetime',
];

  public function getTimeAttribute()
    {
        return $this->start_date->format('H:i');
    }
    
    public function getDateAttribute()
    {
        return $this->start_date->format('Y-m-d');
    }
     public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants()
    {
        return $this->hasMany(EventParticipant::class);
    }
    public function scopeActive($query)
{
    return $query->where('status', 'active')
        ->where('end_date', '>', now());
}
}
