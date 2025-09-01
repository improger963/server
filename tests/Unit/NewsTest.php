<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_news_item()
    {
        $news = News::create([
            'title' => 'Test News',
            'content' => 'This is a test news item.',
            'author' => 'John Doe',
            'is_published' => true,
        ]);
        
        $this->assertDatabaseHas('news', [
            'title' => 'Test News',
            'content' => 'This is a test news item.',
            'author' => 'John Doe',
            'is_published' => true,
        ]);
        
        $this->assertEquals('Test News', $news->title);
        $this->assertTrue($news->is_published);
    }
    
    /** @test */
    public function it_can_scope_published_news()
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
        
        // Create future published news
        News::create([
            'title' => 'Future News',
            'content' => 'This is scheduled for the future.',
            'is_published' => true,
            'published_at' => now()->addDay(),
        ]);
        
        $publishedNews = News::published()->get();
        
        $this->assertCount(1, $publishedNews);
        $this->assertEquals('Published News', $publishedNews->first()->title);
    }
    
    /** @test */
    public function it_automatically_sets_published_at_when_publishing()
    {
        $news = News::create([
            'title' => 'Test News',
            'content' => 'This is a test news item.',
            'is_published' => false,
        ]);
        
        $this->assertNull($news->published_at);
        
        $news->is_published = true;
        $news->save();
        
        $this->assertNotNull($news->published_at);
    }
}