<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    /**
     * Vote for a post (toggle)
     */
    public function vote(Request $request, $postId)
    {
        $validated = $request->validate([
            'is_anonymous' => 'boolean',
        ]);

        $userId = $request->get('authenticated_user_id');

        // Check if post exists and is approved
        $post = Post::where('id', $postId)
            ->where('status', 'approved')
            ->firstOrFail();

        // Check user's preference for anonymous voting
        $user = User::find($userId);
        $isAnonymous = $validated['is_anonymous'] ?? ($user->prefer_anonymous_voting ?? false);

        // Check if user has already voted
        $existingVote = Vote::where('post_id', $postId)
            ->where('user_id', $userId)
            ->first();

        if ($existingVote) {
            // Remove vote
            $existingVote->delete();

            // Get updated count in same query
            $votesCount = Vote::where('post_id', $postId)->count();

            return response()->json([
                'message' => 'Vote removed',
                'voted' => false,
                'votes_count' => $votesCount,
            ]);
        } else {
            // Create vote
            Vote::create([
                'post_id' => $postId,
                'user_id' => $userId,
                'is_anonymous' => $isAnonymous,
            ]);

            // Get votes count
            $votesCount = Vote::where('post_id', $postId)->count();

            return response()->json([
                'message' => 'Vote recorded',
                'voted' => true,
                'is_anonymous' => $isAnonymous,
                'votes_count' => $votesCount,
            ], 201);
        }
    }

    /**
     * Get vote count and user's vote status for a post
     */
    public function getVoteStatus(Request $request, $postId)
    {
        $userId = $request->get('authenticated_user_id');

        $votesCount = Vote::where('post_id', $postId)->count();
        $commentsCount = \App\Models\Comment::where('post_id', $postId)->count();

        $userVote = null;
        if ($userId) {
            $userVote = Vote::where('post_id', $postId)
                ->where('user_id', $userId)
                ->first();
        }

        return response()->json([
            'votes_count' => $votesCount,
            'comments_count' => $commentsCount,
            'user_has_voted' => $userVote !== null,
            'user_vote_is_anonymous' => $userVote ? $userVote->is_anonymous : null,
        ]);
    }
}
