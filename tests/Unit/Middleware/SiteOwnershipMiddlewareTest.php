<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SiteOwnershipMiddleware;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class SiteOwnershipMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SiteOwnershipMiddleware();
    }

    /** @test */
    public function it_allows_owner_to_access_site()
    {
        // Create a user and site
        $user = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $user->id]);

        // Authenticate the user
        $this->actingAs($user);

        // Create a request with the site route parameter
        $request = Request::create("/api/sites/{$site->id}", 'GET');
        $request->route()->setParameter('site', $site->id);

        // Process the middleware
        $response = $this->middleware->handle($request, function () {
            return new Response('', 200);
        });

        // Assert that the response is 200 OK
        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function it_blocks_non_owner_from_accessing_site()
    {
        // Create two users and a site owned by the first user
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $owner->id]);

        // Authenticate the other user
        $this->actingAs($otherUser);

        // Create a request with the site route parameter
        $request = Request::create("/api/sites/{$site->id}", 'GET');
        $request->route()->setParameter('site', $site->id);

        // Process the middleware and expect an exception
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        $this->middleware->handle($request, function () {
            return new Response('', 200);
        });
    }

    /** @test */
    public function it_blocks_access_to_nonexistent_site()
    {
        // Create a user
        $user = User::factory()->create();

        // Authenticate the user
        $this->actingAs($user);

        // Create a request with a non-existent site ID
        $request = Request::create("/api/sites/999999", 'GET');
        $request->route()->setParameter('site', 999999);

        // Process the middleware and expect an exception
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->middleware->handle($request, function () {
            return new Response('', 200);
        });
    }
}