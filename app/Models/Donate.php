<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donate extends Model
{
    use HasFactory;
    protected $fillable = [
    'full_name', 'number', 'email', 'donation_type','amount', 'ammountinkello', 'notes', 'status', 'user_id'
];

      protected $casts = [
        'amount' => 'decimal:2',
      //  'is_approved' => 'boolean'
    ];
public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

}
