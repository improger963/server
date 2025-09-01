<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\CampaignOwnershipMiddleware;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class CampaignOwnershipMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new CampaignOwnershipMiddleware();
    }

    /** @test */
    public function it_allows_owner_to_access_campaign()
    {
        // Create a user and campaign
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);

        // Authenticate the user
        $this->actingAs($user);

        // Create a request with the campaign route parameter
        $request = Request::create("/api/campaigns/{$campaign->id}", 'GET');
        $request->route()->setParameter('campaign', $campaign->id);

        // Process the middleware
        $response = $this->middleware->handle($request, function () {
            return new Response('', 200);
        });

        // Assert that the response is 200 OK
        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function it_blocks_non_owner_from_accessing_campaign()
    {
        // Create two users and a campaign owned by the first user
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $owner->id]);

        // Authenticate the other user
        $this->actingAs($otherUser);

        // Create a request with the campaign route parameter
        $request = Request::create("/api/campaigns/{$campaign->id}", 'GET');
        $request->route()->setParameter('campaign', $campaign->id);

        // Process the middleware and expect an exception
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        $this->middleware->handle($request, function () {
            return new Response('', 200);
        });
    }

    /** @test */
    public function it_blocks_access_to_nonexistent_campaign()
    {
        // Create a user
        $user = User::factory()->create();

        // Authenticate the user
        $this->actingAs($user);

        // Create a request with a non-existent campaign ID
        $request = Request::create("/api/campaigns/999999", 'GET');
        $request->route()->setParameter('campaign', 999999);

        // Process the middleware and expect an exception
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->middleware->handle($request, function () {
            return new Response('', 200);
        });
    }
}