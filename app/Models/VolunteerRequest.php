<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class VolunteerRequest extends Model
{
    use HasFactory;

     protected $fillable = [
      'user_id',
      'cv_path',
      'status',
      'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
