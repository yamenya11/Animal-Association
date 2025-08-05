<?php
namespace App\Services;

use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Ad;
class WalletService
{
    
    public function deposit(User $user, float $amount): float
    {
        return $user->deposit($amount);
    }

 public function withdraw(User $user, float $amount, ?Ad $ad = null): float
{
    $currentBalance = (float) $user->wallet_balance;
    $amount = (float) $amount;

    if ($currentBalance < $amount) {
        throw new \Exception(sprintf(
            'رصيد غير كافي (الرصيد الحالي: %.2f، المبلغ المطلوب: %.2f)',
            $currentBalance,
            $amount
        ));
    }

    $user->wallet_balance = $currentBalance - $amount;
    $user->save();
   
    // تسجيل المعاملة
    WalletTransaction::create([
        'user_id' => $user->id,
        'amount' => $amount,
        'type' => 'withdrawal',
        'description' => 'سحب مقابل إعلان',
         'ad_id' => $ad ? $ad->id : null,
    ]);

    return $user->wallet_balance;
}


     public function getBalance(User $user): float
    {
       return (float) $user->wallet_balance;
    }

}