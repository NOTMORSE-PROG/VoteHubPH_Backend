<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Vote;
use App\Models\CommentLike;

class UserController extends Controller
{
    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        $userId = $request->get('authenticated_user_id');
        $user = DB::table('User')->where('id', $userId)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Get user's posts (all statuses)
        $posts = Post::where('user_id', $userId)
            ->withCount(['votes', 'comments'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'name' => $post->name,
                    'position' => $post->position,
                    'level' => $post->level,
                    'status' => $post->status,
                    'admin_notes' => $post->admin_notes,
                    'profile_photo' => $post->profile_photo,
                    'created_at' => $post->created_at,
                    'updated_at' => $post->updated_at,
                    'votes_count' => $post->votes_count ?? 0,
                    'comments_count' => $post->comments_count ?? 0,
                ];
            });

        // Get user's comments
        $comments = Comment::where('user_id', $userId)
            ->with(['post:id,name,profile_photo'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'post_id' => $comment->post_id,
                    'post_name' => $comment->post->name ?? 'Unknown Post',
                    'post_profile_photo' => $comment->post->profile_photo ?? null,
                    'content' => $comment->content,
                    'is_anonymous' => $comment->is_anonymous,
                    'likes_count' => $comment->likes_count ?? 0,
                    'created_at' => $comment->created_at,
                ];
            });

        // Get user's votes
        $votes = Vote::where('user_id', $userId)
            ->with(['post:id,name,position,level,profile_photo'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($vote) {
                return [
                    'id' => $vote->id,
                    'post_id' => $vote->post_id,
                    'post_name' => $vote->post->name ?? 'Unknown Candidate',
                    'post_position' => $vote->post->position ?? '',
                    'post_level' => $vote->post->level ?? '',
                    'post_profile_photo' => $vote->post->profile_photo ?? null,
                    'is_anonymous' => $vote->is_anonymous,
                    'created_at' => $vote->created_at,
                ];
            });

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'image' => $user->image,
            'provider' => $user->provider,
            'language' => $user->language,
            'profileCompleted' => $user->profileCompleted,
            'createdAt' => $user->createdAt,
            'lastLoginAt' => $user->lastLoginAt,
            'posts' => $posts,
            'comments' => $comments,
            'votes' => $votes,
        ]);
    }

    /**
     * Complete user profile (add location)
     */
    public function completeProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'region' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->get('authenticated_user_id');

        DB::table('User')
            ->where('id', $userId)
            ->update([
                'region' => $request->region,
                'city' => $request->city,
                'barangay' => $request->barangay,
                'profileCompleted' => true,
                'updatedAt' => now(),
            ]);

        $user = DB::table('User')->where('id', $userId)->first();

        return response()->json([
            'message' => 'Profile completed successfully',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'image' => $user->image,
                'provider' => $user->provider,
                'language' => $user->language,
                'profileCompleted' => $user->profileCompleted,
                'location' => [
                    'region' => $user->region,
                    'city' => $user->city,
                    'barangay' => $user->barangay,
                ],
            ],
        ]);
    }

    /**
     * Update user information
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'language' => 'sometimes|string|in:en,fil',
            'image' => 'sometimes|string', // Cloudinary URL
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->get('authenticated_user_id');

        $updateData = [];
        
        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }
        
        if ($request->has('language')) {
            $updateData['language'] = $request->language;
        }
        
        if ($request->has('image')) {
            $updateData['image'] = $request->image;
        }
        
        $updateData['updatedAt'] = now();

        DB::table('User')
            ->where('id', $userId)
            ->update($updateData);

        $user = DB::table('User')->where('id', $userId)->first();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'image' => $user->image,
                'provider' => $user->provider,
                'language' => $user->language,
                'profileCompleted' => $user->profileCompleted,
            ],
        ]);
    }

    /**
     * Delete user account and all associated data
     */
    public function deleteAccount(Request $request)
    {
        $userId = $request->get('authenticated_user_id');

        return DB::transaction(function () use ($userId) {
            // Delete all user's posts (candidates) - this will cascade delete related data
            $posts = Post::where('user_id', $userId)->get();
            
            foreach ($posts as $post) {
                // Delete post images from Cloudinary if needed
                // (You can add Cloudinary deletion here if needed)
                
                // Delete post (cascades to comments, votes, etc.)
                $post->delete();
            }

            // Clean up any empty party lists after posts are deleted
            $this->cleanupEmptyPartyLists();

            // Delete all user's comments
            Comment::where('user_id', $userId)->delete();

            // Delete all user's comment likes
            CommentLike::where('user_id', $userId)->delete();

            // Delete all user's votes
            Vote::where('user_id', $userId)->delete();

            // Delete user's OAuth accounts
            DB::table('Account')->where('userId', $userId)->delete();

            // Delete user's sessions
            DB::table('Session')->where('userId', $userId)->delete();

            // Delete user's OTPs if any
            // (Add if you have an OTP table)

            // Finally, delete the user
            DB::table('User')->where('id', $userId)->delete();

            return response()->json([
                'message' => 'Account deleted successfully'
            ]);
        });
    }

    /**
     * Clean up party lists that have no members
     */
    private function cleanupEmptyPartyLists(): void
    {
        $emptyPartyLists = \App\Models\PartyList::withCount('members')
            ->having('members_count', '=', 0)
            ->get();

        foreach ($emptyPartyLists as $partyList) {
            $partyList->delete();
        }
    }
}
