<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;
      protected $fillable = [
          'user_id',
        'amount',
        'type',
        'description',
        'ad_id'
    ];

     public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }
}
