<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialController extends Controller
{
    /**
     * Deposit funds into user account.
     */
    public function deposit(Request $request)
    {
        $amount = $request->input('amount');
        
        // Validate amount
        if (!is_numeric($amount) || $amount <= 0) {
            return response()->json(['error' => 'Invalid amount'], 400);
        }
        
        // Add amount to user balance
        $user = Auth::user();
        $user->addBalance($amount);
        
        return response()->json(['message' => 'Deposit successful', 'balance' => $user->balance]);
    }

    /**
     * Withdraw funds from user account.
     */
    public function withdraw(Request $request)
    {
        $amount = $request->input('amount');
        
        // Validate amount
        if (!is_numeric($amount) || $amount <= 0) {
            return response()->json(['error' => 'Invalid amount'], 400);
        }
        
        $user = Auth::user();
        
        // Check if user has enough balance
        if (!$user->hasBalance($amount)) {
            return response()->json(['error' => 'Insufficient funds'], 400);
        }
        
        // Deduct amount from user balance
        $user->deductBalance($amount);
        
        return response()->json(['message' => 'Withdrawal successful', 'balance' => $user->balance]);
    }
}