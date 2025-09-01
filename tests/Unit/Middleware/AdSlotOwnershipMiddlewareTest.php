<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\AdSlotOwnershipMiddleware;
use App\Models\AdSlot;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class AdSlotOwnershipMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new AdSlotOwnershipMiddleware();
    }

    /** @test */
    public function it_allows_owner_to_access_ad_slot()
    {
        // Create a user, site, and ad slot
        $user = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $user->id]);
        $adSlot = AdSlot::factory()->create(['site_id' => $site->id]);

        // Authenticate the user
        $this->actingAs($user);

        // Create a request with the ad slot route parameter
        $request = Request::create("/api/sites/{$site->id}/ad-slots/{$adSlot->id}", 'GET');
        $request->route()->setParameter('adSlot', $adSlot->id);

        // Process the middleware
        $response = $this->middleware->handle($request, function () {
            return new Response('', 200);
        });

        // Assert that the response is 200 OK
        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function it_blocks_non_owner_from_accessing_ad_slot()
    {
        // Create two users, a site owned by the first user, and an ad slot
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $owner->id]);
        $adSlot = AdSlot::factory()->create(['site_id' => $site->id]);

        // Authenticate the other user
        $this->actingAs($otherUser);

        // Create a request with the ad slot route parameter
        $request = Request::create("/api/sites/{$site->id}/ad-slots/{$adSlot->id}", 'GET');
        $request->route()->setParameter('adSlot', $adSlot->id);

        // Process the middleware and expect an exception
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        $this->middleware->handle($request, function () {
            return new Response('', 200);
        });
    }

    /** @test */
    public function it_blocks_access_to_nonexistent_ad_slot()
    {
        // Create a user and site
        $user = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $user->id]);

        // Authenticate the user
        $this->actingAs($user);

        // Create a request with a non-existent ad slot ID
        $request = Request::create("/api/sites/{$site->id}/ad-slots/999999", 'GET');
        $request->route()->setParameter('adSlot', 999999);

        // Process the middleware and expect an exception
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->middleware->handle($request, function () {
            return new Response('', 200);
        });
    }
}