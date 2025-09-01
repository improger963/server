<?php

namespace App\Http\Controllers\Api;

use App\Models\AnalyticsEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function dashboard(Request $request)
    {
        $period = $request->input('period', 'month');
        $userId = auth()->id();
        
        // Determine date range based on period
        $startDate = $this->getStartDateForPeriod($period);
        
        // Get aggregated statistics
        $stats = AnalyticsEvent::forUser($userId)
            ->forPeriod($startDate, now())
            ->select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(cost) as total'))
            ->groupBy('type')
            ->get()
            ->keyBy('type');
        
        // Calculate specific metrics
        $impressions = $stats->get('impression', (object)['count' => 0, 'total' => 0]);
        $clicks = $stats->get('click', (object)['count' => 0, 'total' => 0]);
        $spend = $stats->get('spend', (object)['count' => 0, 'total' => 0]);
        $earnings = $stats->get('earning', (object)['count' => 0, 'total' => 0]);
        
        // Calculate CTR
        $ctr = $impressions->count > 0 ? ($clicks->count / $impressions->count) * 100 : 0;
        
        return response()->json([
            'revenue' => $earnings->total,
            'spend' => $spend->total,
            'impressions' => $impressions->count,
            'clicks' => $clicks->count,
            'ctr' => round($ctr, 2),
        ]);
    }
    
    private function getStartDateForPeriod($period)
    {
        switch ($period) {
            case 'today':
                return now()->startOfDay();
            case 'week':
                return now()->startOfWeek();
            case 'year':
                return now()->startOfYear();
            case 'month':
            default:
                return now()->startOfMonth();
        }
    }
}