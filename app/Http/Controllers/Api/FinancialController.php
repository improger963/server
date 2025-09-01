<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransactionLog;
use App\Notifications\BalanceTopUpSuccess;
use App\Services\FinancialService;
use App\Services\PayeerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FinancialController extends Controller
{
    protected $payeerService;
    protected $financialService;

    public function __construct(PayeerService $payeerService, FinancialService $financialService)
    {
        $this->payeerService = $payeerService;
        $this->financialService = $financialService;
    }

    /**
     * Deposit funds into user account with atomic balance updates and enhanced validation.
     */
    public function deposit(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $amount = $request->input('amount');
        $user = Auth::user();
        
        // Use database transaction for atomic balance update
        try {
            DB::beginTransaction();
            
            // Add amount to user balance
            if (!$user->addBalance($amount)) {
                throw new \Exception('Failed to add balance to user account');
            }
            
            // Log the transaction
            TransactionLog::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => 'deposit',
                'reference' => uniqid('DEP_' . $user->id . '_'),
                'status' => 'completed',
                'description' => 'Manual deposit',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            DB::commit();
            
            // Send notification for successful deposit
            $user->notify(new BalanceTopUpSuccess($amount, $user->balance));
            
            return response()->json([
                'message' => 'Deposit successful', 
                'balance' => $user->balance,
                'amount' => $amount
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            
            \Log::error('Deposit failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'amount' => $amount,
                'exception' => $e
            ]);
            
            return response()->json(['error' => 'Deposit failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Withdraw funds from user account using FinancialService with enhanced validation.
     */
    public function withdraw(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $amount = $request->input('amount');
        $user = Auth::user();
        
        // Check if user has enough balance using FinancialService
        if (!$this->financialService->validateWithdrawalAmount($user, $amount)) {
            return response()->json(['error' => 'Insufficient funds'], 400);
        }
        
        // Create withdrawal request using FinancialService
        $result = $this->financialService->createWithdrawal($user, $amount);
        
        if ($result['success']) {
            return response()->json([
                'message' => 'Withdrawal request created successfully', 
                'balance' => $user->balance,
                'frozen_balance' => $user->frozen_balance,
                'amount' => $amount
            ]);
        }
        
        return response()->json(['error' => $result['error']], 400);
    }

    /**
     * Payeer webhook endpoint for deposit processing with enhanced error handling.
     */
    public function payeerWebhook(Request $request)
    {
        // Validate that request is from Payeer (basic check)
        if (!$request->has('m_operation_id') || !$request->has('m_sign')) {
            return response()->json(['status' => 'error', 'message' => 'Invalid request'], 400);
        }

        // Get all request data
        $data = $request->all();
        
        // Process deposit
        $result = $this->payeerService->processDeposit($data);
        
        if ($result['success']) {
            return response()->json(['status' => 'success']);
        }
        
        // Log webhook errors
        \Log::warning('Payeer webhook error: ' . $result['error'], [
            'data' => $data,
            'error' => $result['error']
        ]);
        
        return response()->json(['status' => 'error', 'message' => $result['error']], 400);
    }
}