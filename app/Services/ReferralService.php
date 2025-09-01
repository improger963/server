<?php

namespace App\Services;

use App\Models\User;
use App\Models\TransactionLog;
use App\Models\ReferralEarning;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    /**
     * Calculate and distribute referral earnings based on a transaction
     *
     * @param TransactionLog $transaction
     * @return array
     */
    public function calculateAndDistributeEarnings(TransactionLog $transaction)
    {
        try {
            // Get the user who made the transaction
            $user = $transaction->user;
            
            // Check if the user has a referrer
            if (!$user || !$user->referrer_id) {
                return [
                    'success' => true,
                    'message' => 'User has no referrer'
                ];
            }
            
            // Get the referrer user
            $referrer = User::find($user->referrer_id);
            
            if (!$referrer) {
                return [
                    'success' => true,
                    'message' => 'Referrer not found'
                ];
            }
            
            // Calculate referral reward (1% of transaction amount)
            $rewardAmount = $transaction->amount * 0.01;
            
            // Use database transaction for atomic operations
            DB::beginTransaction();
            
            // Add the reward amount to the referrer's balance
            $referrer->addBalance($rewardAmount);
            
            // Create a record in the referral_earnings table
            ReferralEarning::create([
                'user_id' => $referrer->id,
                'referred_user_id' => $user->id,
                'amount' => $rewardAmount,
                'source_transaction_id' => $transaction->id,
                'type' => $transaction->isDeposit() ? 'deposit' : 'ad_spend',
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Referral earnings distributed successfully',
                'amount' => $rewardAmount
            ];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Referral earnings distribution error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Referral earnings distribution failed'
            ];
        }
    }
    
    /**
     * Generate a unique referral code for a user
     *
     * @param User $user
     * @return string
     */
    public function generateReferralCode(User $user)
    {
        // Generate a unique referral code based on user ID and random string
        return 'REF' . strtoupper(substr(md5($user->id . time()), 0, 8));
    }
    
    /**
     * Get referral statistics for a user
     *
     * @param User $user
     * @return array
     */
    public function getReferralStats(User $user)
    {
        $referredUsersCount = $user->referrals()->count();
        
        $totalEarnings = $user->referralEarnings()->sum('amount');
        
        return [
            'referral_code' => $user->referral_code,
            'referred_users_count' => $referredUsersCount,
            'total_earnings' => $totalEarnings,
        ];
    }
}