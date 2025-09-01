<?php

namespace App\Services;

use App\Models\User;
use App\Models\TransactionLog;
use Illuminate\Support\Facades\Log;

class PayeerService
{
    private $merchantId;
    private $secretKey;
    private $baseUrl;

    public function __construct()
    {
        $this->merchantId = config('services.payeer.merchant_id');
        $this->secretKey = config('services.payeer.secret_key');
        $this->baseUrl = config('services.payeer.base_url', 'https://payeer.com/merchant/');
    }

    /**
     * Initiate a deposit request with Payeer
     *
     * @param User $user
     * @param float $amount
     * @param string $description
     * @return array
     */
    public function initiateDeposit(User $user, $amount, $description = 'SmartLink Deposit')
    {
        // Validate amount
        if (!is_numeric($amount) || $amount <= 0) {
            return [
                'success' => false,
                'error' => 'Invalid amount'
            ];
        }

        // Generate order ID
        $orderId = uniqid('DEP_' . $user->id . '_');

        // Prepare data for Payeer
        $data = [
            'm_shop' => $this->merchantId,
            'm_orderid' => $orderId,
            'm_amount' => number_format($amount, 2, '.', ''),
            'm_curr' => 'USD',
            'm_desc' => base64_encode($description),
        ];

        // Generate signature
        $data['m_sign'] = $this->generateSignature($data);

        // Log the transaction
        TransactionLog::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'type' => 'deposit',
            'reference' => $orderId,
            'status' => 'pending',
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return [
            'success' => true,
            'order_id' => $orderId,
            'data' => $data,
            'redirect_url' => $this->baseUrl
        ];
    }

    /**
     * Verify webhook signature from Payeer
     *
     * @param array $data
     * @return bool
     */
    public function verifyWebhook($data)
    {
        // Check if required fields are present
        if (!isset($data['m_operation_id']) || !isset($data['m_sign'])) {
            return false;
        }

        // Extract signature and remove from data
        $signature = $data['m_sign'];
        unset($data['m_sign']);

        // Generate expected signature
        $expectedSignature = $this->generateSignature($data);

        // Compare signatures
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Process deposit from Payeer webhook with enhanced logging and verification
     *
     * @param array $data
     * @return array
     */
    public function processDeposit($data)
    {
        // Log incoming webhook data for debugging
        Log::info('Payeer webhook received', ['data' => $data]);

        // Verify webhook data
        if (!$this->verifyWebhook($data)) {
            Log::warning('Payeer webhook verification failed', ['data' => $data]);
            return [
                'success' => false,
                'error' => 'Invalid signature'
            ];
        }

        // Check if transaction already exists
        $transaction = TransactionLog::where('reference', $data['m_orderid'])
            ->where('type', 'deposit')
            ->first();

        if ($transaction) {
            if ($transaction->status === 'completed') {
                Log::info('Payeer transaction already processed', ['order_id' => $data['m_orderid']]);
                return [
                    'success' => false,
                    'error' => 'Transaction already processed'
                ];
            } elseif ($transaction->status === 'failed') {
                Log::info('Payeer transaction previously failed, will retry', ['order_id' => $data['m_orderid']]);
                // Continue processing for failed transactions
            }
        }

        // Validate amount
        $amount = floatval($data['m_amount']);
        if ($amount <= 0) {
            Log::warning('Payeer invalid amount', ['amount' => $amount]);
            return [
                'success' => false,
                'error' => 'Invalid amount'
            ];
        }

        // Validate currency
        if ($data['m_curr'] !== 'USD') {
            Log::warning('Payeer invalid currency', ['currency' => $data['m_curr']]);
            return [
                'success' => false,
                'error' => 'Invalid currency'
            ];
        }

        // Extract user ID from order ID
        $orderId = $data['m_orderid'];
        $parts = explode('_', $orderId);
        if (count($parts) < 3 || !is_numeric($parts[1])) {
            Log::error('Payeer invalid order ID format', ['order_id' => $orderId]);
            return [
                'success' => false,
                'error' => 'Invalid order ID'
            ];
        }

        $userId = $parts[1];
        $user = User::find($userId);

        if (!$user) {
            Log::error('Payeer user not found', ['user_id' => $userId]);
            return [
                'success' => false,
                'error' => 'User not found'
            ];
        }

        // Start database transaction
        try {
            \DB::beginTransaction();

            // Add balance to user
            $user->addBalance($amount);

            // Update or create transaction log
            if ($transaction) {
                $transaction->update([
                    'status' => 'completed',
                    'description' => 'Payeer deposit completed via webhook',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            } else {
                TransactionLog::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'type' => 'deposit',
                    'reference' => $orderId,
                    'status' => 'completed',
                    'description' => 'Payeer deposit completed via webhook',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            \DB::commit();

            Log::info('Payeer deposit processed successfully', [
                'user_id' => $user->id,
                'amount' => $amount,
                'order_id' => $orderId
            ]);

            return [
                'success' => true,
                'message' => 'Deposit processed successfully',
                'user_id' => $user->id,
                'amount' => $amount
            ];
        } catch (\Exception $e) {
            \DB::rollback();
            Log::error('Payeer deposit processing error: ' . $e->getMessage(), [
                'exception' => $e,
                'order_id' => $orderId
            ]);

            // Update transaction log with failed status
            if ($transaction) {
                $transaction->update([
                    'status' => 'failed',
                    'description' => 'Payeer deposit failed: ' . $e->getMessage()
                ]);
            }

            return [
                'success' => false,
                'error' => 'Processing error'
            ];
        }
    }

    /**
     * Generate signature for Payeer requests
     *
     * @param array $data
     * @return string
     */
    private function generateSignature($data)
    {
        ksort($data);
        $values = array_values($data);
        $values[] = $this->secretKey;
        $signature = implode(':', $values);
        return hash('sha256', $signature);
    }
}