<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdMedia extends Model
{
    use HasFactory;
    
    protected $fillable = ['ad_id', 'media_path', 'media_type'];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }
}
