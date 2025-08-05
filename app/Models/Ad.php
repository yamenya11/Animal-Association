<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;
     protected $fillable = [
        'user_id', 'title', 'description', 'price', 
        'status', 'approved_by', 'approved_at'
    ];

     public function user()
    {
        return $this->belongsTo(User::class);
    }

     public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function media()
{
    return $this->hasMany(AdMedia::class);
}
public function walletTransactions()
{
    return $this->hasMany(WalletTransaction::class, 'ad_id');
}
}
