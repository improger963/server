<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BalanceTopUpSuccess extends Notification
{
    use Queueable;
    
    protected $amount;
    protected $transactionId;
    
    public function __construct($amount, $transactionId)
    {
        $this->amount = $amount;
        $this->transactionId = $transactionId;
    }
    
    public function via($notifiable): array
    {
        return ['database'];
    }
    
    public function toArray($notifiable): array
    {
        return [
            'amount' => $this->amount,
            'transaction_id' => $this->transactionId,
            'balance_after' => $notifiable->balance,
        ];
    }
}