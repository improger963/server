<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Withdrawal;
use App\Notifications\CampaignBudgetWarning;
use App\Notifications\BalanceTopUpSuccess;
use App\Notifications\WithdrawalApproved;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_send_campaign_budget_warning_notification()
    {
        Notification::fake();
        
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Campaign',
            'budget' => 100.00,
            'spent' => 80.00,
        ]);
        
        $notification = new CampaignBudgetWarning($campaign);
        $user->notify($notification);
        
        Notification::assertSentTo($user, CampaignBudgetWarning::class);
        
        $array = $notification->toArray($user);
        
        $this->assertEquals($campaign->id, $array['campaign_id']);
        $this->assertEquals($campaign->name, $array['campaign_name']);
        $this->assertEquals(80.00, $array['budget_used']);
        $this->assertEquals(100.00, $array['budget_total']);
        $this->assertEquals(80.00, $array['percentage_used']);
    }
    
    /** @test */
    public function it_can_send_balance_top_up_success_notification()
    {
        Notification::fake();
        
        $user = User::factory()->create();
        $amount = 50.00;
        $transactionId = 'TXN_12345';
        
        $notification = new BalanceTopUpSuccess($amount, $transactionId);
        $user->notify($notification);
        
        Notification::assertSentTo($user, BalanceTopUpSuccess::class);
        
        $array = $notification->toArray($user);
        
        $this->assertEquals($amount, $array['amount']);
        $this->assertEquals($transactionId, $array['transaction_id']);
        $this->assertEquals($user->balance, $array['balance_after']);
    }
    
    /** @test */
    public function it_can_send_withdrawal_approved_notification()
    {
        Notification::fake();
        
        $user = User::factory()->create();
        $withdrawal = Withdrawal::factory()->create([
            'user_id' => $user->id,
            'amount' => 25.00,
            'processed_at' => now(),
        ]);
        
        $notification = new WithdrawalApproved($withdrawal);
        $user->notify($notification);
        
        Notification::assertSentTo($user, WithdrawalApproved::class);
        
        $array = $notification->toArray($user);
        
        $this->assertEquals($withdrawal->id, $array['withdrawal_id']);
        $this->assertEquals($withdrawal->amount, $array['amount']);
        $this->assertEquals($withdrawal->processed_at, $array['processed_at']);
    }
}