<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Withdrawal;

class WithdrawalApproved extends Notification
{
    use Queueable;
    
    protected $withdrawal;
    
    public function __construct(Withdrawal $withdrawal)
    {
        $this->withdrawal = $withdrawal;
    }
    
    public function via($notifiable): array
    {
        return ['database'];
    }
    
    public function toArray($notifiable): array
    {
        return [
            'withdrawal_id' => $this->withdrawal->id,
            'amount' => $this->withdrawal->amount,
            'processed_at' => $this->withdrawal->processed_at,
        ];
    }
}