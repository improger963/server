<?php

namespace App\Http\Controllers\Api;

use App\Models\News;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::published()
            ->latest('published_at')
            ->paginate(10);
            
        return response()->json($news);
    }
}