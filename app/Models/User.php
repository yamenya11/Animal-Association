<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Adoption;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;
use App\Models\VolunteerRequest;
use App\Models\AnimalCase;
use Stephenjude\Wallet\Traits\HasWallet;
use Stephenjude\Wallet\Interfaces\Wallet;

class User extends Authenticatable implements Wallet
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles,HasWallet;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'wallet_balance'
    ];

    public function adoptions()
   {
    return $this->hasMany(Adoption::class);
   }

   public function posts() {
    return $this->hasMany(Post::class);
    }

public function comments() {
    return $this->hasMany(Comment::class);
    }

public function likes() {
    return $this->hasMany(Like::class);
    }

    public function volinter() {
    return $this->hasMany(User::class);
    }
    
     public function animal_cases() {
    return $this->hasMany(AnimalCase::class);
}
    
 public function ads()
    {
        return $this->hasMany(Ad::class);
    }
    
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
