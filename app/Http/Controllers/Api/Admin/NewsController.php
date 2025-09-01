<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::latest()->paginate(20);
        return response()->json($news);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'author' => 'nullable|string|max:255',
            'is_published' => 'boolean',
        ]);
        
        $news = News::create($validated);
        
        return response()->json($news, 201);
    }
    
    public function show(News $news)
    {
        return response()->json($news);
    }
    
    public function update(Request $request, News $news)
    {
        $validated = $request->validate([
            'title' => 'string|max:255',
            'content' => 'string',
            'author' => 'nullable|string|max:255',
            'is_published' => 'boolean',
        ]);
        
        $news->update($validated);
        
        return response()->json($news);
    }
    
    public function destroy(News $news)
    {
        $news->delete();
        
        return response()->json(['message' => 'News deleted successfully']);
    }
}