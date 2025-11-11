<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Comment;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * Get platform statistics
     */
    public function getPlatformStats()
    {
        try {
            // Total registered users (politicians)
            $totalUsers = User::count();

            // Total discussions (comments)
            $totalDiscussions = Comment::count();

            // Total votes cast
            $totalVotes = Vote::count();

            // User distribution by region
            $regionDistribution = User::select('region', DB::raw('COUNT(*) as count'))
                ->whereNotNull('region')
                ->groupBy('region')
                ->orderBy('count', 'DESC')
                ->get();

            // Most active users (by comments)
            $mostActiveUsers = User::select('users.id', 'users.name', DB::raw('COUNT(comments.id) as comment_count'))
                ->leftJoin('comments', 'users.id', '=', 'comments.user_id')
                ->groupBy('users.id', 'users.name')
                ->orderBy('comment_count', 'DESC')
                ->limit(10)
                ->get();

            // Recent activity counts (last 30 days)
            $recentUsers = User::where('created_at', '>=', now()->subDays(30))->count();
            $recentComments = Comment::where('created_at', '>=', now()->subDays(30))->count();
            $recentVotes = Vote::where('created_at', '>=', now()->subDays(30))->count();

            return response()->json([
                'platform' => [
                    'total_users' => $totalUsers,
                    'total_discussions' => $totalDiscussions,
                    'total_votes' => $totalVotes,
                ],
                'recent_activity' => [
                    'new_users_30d' => $recentUsers,
                    'new_discussions_30d' => $recentComments,
                    'new_votes_30d' => $recentVotes,
                ],
                'region_distribution' => $regionDistribution,
                'most_active_users' => $mostActiveUsers,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get candidate statistics by position
     */
    public function getCandidateStats()
    {
        try {
            // This would need a candidates table in the future
            // For now, return basic user statistics

            $usersByLanguage = User::select('language', DB::raw('COUNT(*) as count'))
                ->groupBy('language')
                ->get();

            $usersByProvider = User::select('provider', DB::raw('COUNT(*) as count'))
                ->groupBy('provider')
                ->get();

            return response()->json([
                'by_language' => $usersByLanguage,
                'by_provider' => $usersByProvider,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch candidate statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
