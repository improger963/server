<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewsControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_published_news()
    {
        // Create published news
        News::create([
            'title' => 'Published News',
            'content' => 'This is published.',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
        
        // Create unpublished news
        News::create([
            'title' => 'Unpublished News',
            'content' => 'This is not published.',
            'is_published' => false,
        ]);
        
        $response = $this->getJson('/api/news');
        
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
        
        $response->assertJsonFragment(['title' => 'Published News']);
        $response->assertJsonMissing(['title' => 'Unpublished News']);
    }
    
    /** @test */
    public function it_can_get_news_with_pagination()
    {
        // Create 15 news items
        News::factory()->count(15)->create([
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
        
        $response = $this->getJson('/api/news?page=1');
        
        $response->assertStatus(200)
                 ->assertJsonCount(10, 'data'); // Default pagination is 10
        
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
    }
    
    /** @test */
    public function it_can_get_admin_news()
    {
        $user = User::factory()->create();
        
        // Make the user an admin by giving them the admin role
        // This would depend on your specific implementation
        
        // Create some news
        News::create([
            'title' => 'Test News',
            'content' => 'This is a test.',
        ]);
        
        // For now, we'll just test that the endpoint exists
        // A full admin test would require authentication and authorization
        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/admin/news');
        
        // This might fail if proper admin authentication is required
        // Adjust according to your admin authentication implementation
        $response->assertStatus(200);
    }
}