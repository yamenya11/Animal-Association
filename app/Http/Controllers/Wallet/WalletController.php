<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\WalletService;

class WalletController extends Controller
{

    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }
    
  public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $user = auth()->user();
        $amount = $request->input('amount');

        $balance = $this->walletService->deposit($user, $amount);

        return response()->json([
            'message' => 'Deposit successful!',
            'balance' => $balance
        ]);
    }

     public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $user = auth()->user();
        $amount = $request->input('amount');

        try {
            $balance = $this->walletService->withdraw($user, $amount);

            return response()->json([
                'message' => 'Withdrawal successful!',
                'balance' => $balance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Withdrawal failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    public function balance()
    {
        $user = auth()->user();

        $balance = $this->walletService->getBalance($user);

        return response()->json([
            'balance' => $balance
        ]);
    }


}






/*
  
  if ($user->wallet_balance < $price) {
    return response()->json(['status' => false, 'message' => 'الرصيد غير كافٍ']);
}

// خصم الرصيد
$user->deductFromWallet($price);
 */ 