<?php
namespace App\Services;

use App\Models\User;

class WalletService
{
    
    public function deposit(User $user, float $amount): float
    {
        return $user->deposit($amount);
    }

     public function withdraw(User $user, float $amount): float
    {
        try {
        // خصم الرصيد من المستخدم
        $newBalance = $user->withdraw($amount);

        // تحويل المبلغ لحساب الأدمن
        $admin = User::where('role', 'admin')->first(); // أو where('id', 1)
        if ($admin) {
            $admin->deposit($amount);
        }

        return $newBalance;

    } catch (InsufficientFundException $e) {
        throw new \Exception("Insufficient funds");
    }
    }

     public function getBalance(User $user): float
    {
        return $user->balance();
    }

}