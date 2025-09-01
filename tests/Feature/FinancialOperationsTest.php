<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FinancialOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user with some balance
        $this->user = User::factory()->create([
            'balance' => 1000.00,
            'frozen_balance' => 0.00
        ]);
    }

    /** @test */
    public function user_can_deposit_funds()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/deposit', [
            'amount' => 500.00
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Deposit successful',
            'amount' => 500.00
        ]);

        // Refresh user from database
        $this->user->refresh();

        // Check that balance was updated
        $this->assertEquals(1500.00, $this->user->balance);
    }

    /** @test */
    public function user_can_withdraw_funds()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/withdraw', [
            'amount' => 300.00
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Withdrawal request created successfully',
            'amount' => 300.00
        ]);

        // Refresh user from database
        $this->user->refresh();

        // Check that balance was reduced and frozen balance was increased
        $this->assertEquals(700.00, $this->user->balance);
        $this->assertEquals(300.00, $this->user->frozen_balance);

        // Check that withdrawal record was created
        $this->assertDatabaseHas('withdrawals', [
            'user_id' => $this->user->id,
            'amount' => 300.00,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function user_cannot_withdraw_more_than_balance()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/withdraw', [
            'amount' => 1500.00
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Insufficient funds'
        ]);

        // Refresh user from database
        $this->user->refresh();

        // Check that balance was not changed
        $this->assertEquals(1000.00, $this->user->balance);
        $this->assertEquals(0.00, $this->user->frozen_balance);
    }

    /** @test */
    public function user_cannot_deposit_invalid_amount()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/deposit', [
            'amount' => -50.00
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function user_cannot_withdraw_invalid_amount()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/withdraw', [
            'amount' => 0
        ]);

        $response->assertStatus(422);
    }
}