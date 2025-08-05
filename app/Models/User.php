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
use App\Models\Donate;
use App\Models\VolunteerRequest;
use App\Models\AnimalCase;
use Stephenjude\Wallet\Traits\HasWallet;
use Stephenjude\Wallet\Interfaces\Wallet;
use Stephenjude\Wallet\Exceptions\InsufficientFundException;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\Report;
use App\Models\TemporaryCareRequest;

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
    'profile_image',
    'address',
    'phone',
    'level',
    'wallet_balance',
     'experience',
    'bio'
];
        public function notifications()
        {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')
                    ->orderBy('created_at', 'desc');
        }

    public function reports()
        {
            return $this->hasMany(Report::class, 'doctor_id');
        }
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

        public function volunteerRequest()
        {
            return $this->hasOne(VolunteerRequest::class);
        }
      public function isVolunteer(): bool
        {
            return $this->volunteerRequest()->where('status', 'approved')->exists();
        }

            
            public function animal_cases() {
            return $this->hasMany(AnimalCase::class);
        }
  
        public function donate()
        {
            return $this->hasMany(Donate::class);
        }
        public function ads()
        {
            return $this->hasMany(Ad::class);
        }

        public function walletTransactions()
        {
            return $this->hasMany(WalletTransaction::class);
        }

            public function isDoctor(): bool
        {
            return $this->hasRole('doctor');
        }

            public function hasSufficientFunds(float $amount): bool
        {
            return $this->balanceFloat >= $amount;
        }

        // تأكد من وجود هذه العلاقة
        public function employeeAppointments()
        {
        return $this->hasMany(Appointment::class, 'employee_id');
        }

        // وإذا كنت تستخدم الصلاحيات
        public function isEmployee(): bool
        {
            return $this->role === 'employee';
        }
    public function temporary()
        {
            return $this->hasMany(TemporaryCareRequest::class);
        }


        // في User model
        public function scopeAvailableEmployees($query)
        {
            return $query->where('role', 'employee')
                        ->where('available', true)
                        ->orderBy('current_workload'); // إذا كنت تتبع عبء العمل
        }

public function withdrawFloat(float $amount, array $meta = [])
{
    if (!$this->hasSufficientFunds($amount)) {
        throw new InsufficientFundException('رصيد غير كافي');
    }
    $this->withdraw($amount, $meta);
    // تسجيل المعاملة يدويًا إذا لم تسجل تلقائيًا
    WalletTransaction::create([
        'user_id' => $this->id,
        'amount' => $amount,
        'type' => 'withdrawal',
        'description' => $meta['description'] ?? 'سحب رصيد',
        'ad_id' => $meta['ad_id'] ?? null
    ]);
}

public function depositFloat(float $amount, array $meta = [])
{
    $this->deposit($amount, $meta);
    
    WalletTransaction::create([
        'user_id' => $this->id,
        'amount' => $amount,
        'type' => 'deposit',
        'description' => $meta['description'] ?? 'إيداع رصيد',
        'ad_id' => $meta['ad_id'] ?? null
    ]);
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
