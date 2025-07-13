<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
Use App\Models\Event;
class EventParticipant extends Model
{
    use HasFactory;
     protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'notes'
    ];

     public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
