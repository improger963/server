<?php

namespace App\Services;

use App\Models\User;
use App\Models\Withdrawal;
use App\Models\TransactionLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancialService
{
    /**
     * Create a withdrawal request for a user
     *
     * @param User $user
     * @param float $amount
     * @return array
     */
    public function createWithdrawal(User $user, $amount)
    {
        // Validate amount
        if (!is_numeric($amount) || $amount <= 0) {
            return [
                'success' => false,
                'error' => 'Invalid amount'
            ];
        }

        // Check if user has enough available balance (excluding frozen balance)
        if (!$user->hasBalance($amount)) {
            return [
                'success' => false,
                'error' => 'Insufficient funds'
            ];
        }

        // Start database transaction
        try {
            DB::beginTransaction();

            // Freeze the amount from user balance
            if (!$user->freezeBalance($amount)) {
                throw new \Exception('Failed to freeze balance');
            }

            // Create withdrawal record
            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'status' => 'pending',
                'transaction_id' => uniqid('WTH_' . $user->id . '_'),
            ]);

            // Log the transaction
            TransactionLog::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => 'withdrawal',
                'reference' => $withdrawal->transaction_id,
                'status' => 'pending',
                'description' => 'Withdrawal request created',
            ]);

            DB::commit();

            return [
                'success' => true,
                'withdrawal' => $withdrawal
            ];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Withdrawal creation error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Withdrawal creation failed'
            ];
        }
    }

    /**
     * Approve a withdrawal request
     *
     * @param Withdrawal $withdrawal
     * @param string $notes
     * @return array
     */
    public function approveWithdrawal(Withdrawal $withdrawal, $notes = null)
    {
        // Check if withdrawal is pending
        if (!$withdrawal->isPending()) {
            return [
                'success' => false,
                'error' => 'Withdrawal is not pending'
            ];
        }

        // Start database transaction
        try {
            DB::beginTransaction();

            // Update withdrawal status
            $withdrawal->update([
                'status' => 'approved',
                'processed_at' => now(),
                'notes' => $notes,
            ]);

            // Log the transaction
            TransactionLog::create([
                'user_id' => $withdrawal->user_id,
                'amount' => $withdrawal->amount,
                'type' => 'withdrawal',
                'reference' => $withdrawal->transaction_id,
                'status' => 'approved',
                'description' => 'Withdrawal approved',
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Withdrawal approved successfully'
            ];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Withdrawal approval error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Withdrawal approval failed'
            ];
        }
    }

    /**
     * Reject a withdrawal request
     *
     * @param Withdrawal $withdrawal
     * @param string $notes
     * @return array
     */
    public function rejectWithdrawal(Withdrawal $withdrawal, $notes = null)
    {
        // Check if withdrawal is pending
        if (!$withdrawal->isPending()) {
            return [
                'success' => false,
                'error' => 'Withdrawal is not pending'
            ];
        }

        // Start database transaction
        try {
            DB::beginTransaction();

            // Unfreeze the amount back to user balance
            $user = $withdrawal->user;
            if (!$user->unfreezeBalance($withdrawal->amount)) {
                throw new \Exception('Failed to unfreeze balance');
            }

            // Update withdrawal status
            $withdrawal->update([
                'status' => 'rejected',
                'processed_at' => now(),
                'notes' => $notes,
            ]);

            // Log the transaction
            TransactionLog::create([
                'user_id' => $withdrawal->user_id,
                'amount' => $withdrawal->amount,
                'type' => 'withdrawal',
                'reference' => $withdrawal->transaction_id,
                'status' => 'rejected',
                'description' => 'Withdrawal rejected',
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Withdrawal rejected successfully'
            ];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Withdrawal rejection error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Withdrawal rejection failed'
            ];
        }
    }

    /**
     * Process a withdrawal (complete the transaction)
     *
     * @param Withdrawal $withdrawal
     * @param string $notes
     * @return array
     */
    public function processWithdrawal(Withdrawal $withdrawal, $notes = null)
    {
        // Check if withdrawal is approved
        if (!$withdrawal->isApproved()) {
            return [
                'success' => false,
                'error' => 'Withdrawal is not approved'
            ];
        }

        // Start database transaction
        try {
            DB::beginTransaction();

            // Deduct the frozen amount from user's frozen balance
            $user = $withdrawal->user;
            if (!$user->unfreezeBalance($withdrawal->amount)) {
                throw new \Exception('Failed to deduct frozen balance');
            }

            // Update withdrawal status
            $withdrawal->update([
                'status' => 'processed',
                'processed_at' => now(),
                'notes' => $notes,
            ]);

            // Log the transaction
            TransactionLog::create([
                'user_id' => $withdrawal->user_id,
                'amount' => $withdrawal->amount,
                'type' => 'withdrawal',
                'reference' => $withdrawal->transaction_id,
                'status' => 'processed',
                'description' => 'Withdrawal processed',
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Withdrawal processed successfully'
            ];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Withdrawal processing error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Withdrawal processing failed'
            ];
        }
    }

    /**
     * Validate withdrawal amount against user's available balance
     *
     * @param User $user
     * @param float $amount
     * @return bool
     */
    public function validateWithdrawalAmount(User $user, $amount)
    {
        // Validate amount
        if (!is_numeric($amount) || $amount <= 0) {
            return false;
        }

        // Check if user has enough available balance
        return $user->hasBalance($amount);
    }

    /**
     * Get user's available balance for withdrawal (excluding frozen amounts)
     *
     * @param User $user
     * @return float
     */
    public function getAvailableBalanceForWithdrawal(User $user)
    {
        return $user->getAvailableBalance();
    }
}