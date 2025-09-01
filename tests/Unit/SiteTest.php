<?php

namespace Tests\Unit;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_site()
    {
        $user = User::factory()->create();
        
        $site = Site::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://example.com',
            'name' => 'Example Site',
            'verified_status' => false,
        ]);

        $this->assertDatabaseHas('sites', [
            'user_id' => $user->id,
            'url' => 'https://example.com',
            'name' => 'Example Site',
            'verified_status' => false,
        ]);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $site->user);
        $this->assertEquals($user->id, $site->user->id);
    }

    /** @test */
    public function it_can_generate_a_verification_token()
    {
        $site = Site::factory()->create();
        $token = $site->generateVerificationToken();

        $this->assertNotNull($token);
        $this->assertEquals(32, strlen($token));
        $this->assertEquals($token, $site->verification_token);
    }

    /** @test */
    public function it_can_verify_a_site_with_correct_token()
    {
        $site = Site::factory()->create(['verification_token' => 'abc123']);
        $result = $site->verifySite('abc123');

        $this->assertTrue($result);
        $this->assertTrue($site->verified_status);
        $this->assertNull($site->verification_token);
    }

    /** @test */
    public function it_cannot_verify_a_site_with_incorrect_token()
    {
        $site = Site::factory()->create(['verification_token' => 'abc123']);
        $result = $site->verifySite('wrongtoken');

        $this->assertFalse($result);
        $this->assertFalse($site->verified_status);
        $this->assertEquals('abc123', $site->verification_token);
    }
}