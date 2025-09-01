<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Campaign;
use App\Models\Withdrawal;
use App\Notifications\CampaignBudgetWarning;
use App\Notifications\BalanceTopUpSuccess;
use App\Notifications\WithdrawalApproved;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_sends_campaign_budget_warning_notification()
    {
        Notification::fake();

        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'budget' => 1000,
            'spent' => 850, // 85% spent
        ]);

        $this->user->notify(new CampaignBudgetWarning($campaign, 150, 85));

        Notification::assertSentTo(
            $this->user,
            CampaignBudgetWarning::class,
            function ($notification, $channels) use ($campaign) {
                $data = $notification->toArray($this->user);
                return $data['campaign_id'] === $campaign->id &&
                       $data['campaign_name'] === $campaign->name &&
                       $data['remaining_budget'] === 150 &&
                       $data['spent_percentage'] === 85;
            }
        );
    }

    /** @test */
    public function it_sends_balance_top_up_success_notification()
    {
        Notification::fake();

        $this->user->notify(new BalanceTopUpSuccess(100, 500));

        Notification::assertSentTo(
            $this->user,
            BalanceTopUpSuccess::class,
            function ($notification, $channels) {
                $data = $notification->toArray($this->user);
                return $data['amount'] === 100 &&
                       $data['new_balance'] === 500;
            }
        );
    }

    /** @test */
    public function it_sends_withdrawal_approved_notification()
    {
        Notification::fake();

        $withdrawal = Withdrawal::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 100,
        ]);

        $this->user->notify(new WithdrawalApproved($withdrawal));

        Notification::assertSentTo(
            $this->user,
            WithdrawalApproved::class,
            function ($notification, $channels) use ($withdrawal) {
                $data = $notification->toArray($this->user);
                return $data['withdrawal_id'] === $withdrawal->id &&
                       $data['amount'] === 100;
            }
        );
    }

    /** @test */
    public function it_can_retrieve_notifications_via_api()
    {
        $this->actingAs($this->user, 'sanctum');

        // Create a notification
        $this->user->notify(new BalanceTopUpSuccess(100, 500));

        $response = $this->getJson('/api/notifications');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'type',
                             'data',
                             'read_at',
                             'created_at'
                         ]
                     ]
                 ]);
    }

    /** @test */
    public function it_can_mark_notification_as_read_via_api()
    {
        $this->actingAs($this->user, 'sanctum');

        // Create a notification
        $this->user->notify(new BalanceTopUpSuccess(100, 500));
        $notification = $this->user->notifications()->first();

        $response = $this->postJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Notification marked as read'
                 ]);

        $this->assertNotNull($notification->refresh()->read_at);
    }
}