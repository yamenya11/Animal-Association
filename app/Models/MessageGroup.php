<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class MessageGroup extends Model
{
    use HasFactory;
    protected $fillable = ['body', 'type', 'user_id', 'conversation_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(Recipient::class);
    }
}
