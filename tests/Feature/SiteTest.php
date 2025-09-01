<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_sites()
    {
        $user = User::factory()->create();
        $sites = Site::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/sites');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_create_a_site()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/sites', [
                'url' => 'https://example.com',
                'name' => 'Example Site',
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'url', 'name', 'verified_status', 'verification_token']);

        $this->assertDatabaseHas('sites', [
            'url' => 'https://example.com',
            'name' => 'Example Site',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_can_verify_a_site()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create([
            'user_id' => $user->id,
            'verification_token' => 'abc123',
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson("/api/sites/{$site->id}/verify", [
                'token' => 'abc123',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Site verified successfully']);

        $this->assertDatabaseHas('sites', [
            'id' => $site->id,
            'verified_status' => true,
            'verification_token' => null,
        ]);
    }

    /** @test */
    public function it_cannot_verify_a_site_with_wrong_token()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create([
            'user_id' => $user->id,
            'verification_token' => 'abc123',
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson("/api/sites/{$site->id}/verify", [
                'token' => 'wrongtoken',
            ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Invalid verification token']);

        $this->assertDatabaseHas('sites', [
            'id' => $site->id,
            'verified_status' => false,
            'verification_token' => 'abc123',
        ]);
    }
}