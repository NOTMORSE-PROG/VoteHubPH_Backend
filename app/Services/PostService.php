<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PartyList;
use App\Models\PartyListMember;
use Illuminate\Support\Facades\DB;

class PostService
{
    /**
     * Create a new post
     *
     * @param array $data
     * @param string $userId
     * @return Post
     */
    public function createPost(array $data, string $userId): Post
    {
        return DB::transaction(function () use ($data, $userId) {
            // Process images if they exist
            $processedImages = null;
            if (isset($data['images']) && is_array($data['images']) && count($data['images']) > 0) {
                $processedImages = [];
                foreach ($data['images'] as $image) {
                    $processedImages[] = [
                        'url' => $image['file'] ?? null,
                        'caption' => $image['caption'] ?? '',
                    ];
                }
            }

            return Post::create([
                'user_id' => $userId,
                'name' => $data['name'],
                'level' => $data['level'],
                'position' => $data['position'],
                'bio' => $data['bio'],
                'platform' => $data['platform'] ?? null,
                'education' => $data['education'] ?? null,
                'achievements' => $data['achievements'] ?? null,
                'images' => $processedImages,
                'profile_photo' => $data['profile_photo'] ?? null,
                'party' => $data['party'] ?? null,
                'city_id' => $data['city_id'] ?? null,
                'district_id' => $data['district_id'] ?? null,
                'barangay_id' => $data['barangay_id'] ?? null,
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Get all posts with user relationship
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPosts()
    {
        return Post::with('user:id,name,email')
            ->with('partyListMember')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($post) {
                $postArray = $post->toArray();
                // Check if party list has been managed (post is linked to a party list)
                $postArray['party_list_managed'] = $post->partyListMember !== null;
                return $postArray;
            });
    }

    /**
     * Get pending posts
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingPosts()
    {
        return Post::where('status', 'pending')
            ->with('user:id,name,email')
            ->with('partyListMember')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($post) {
                $postArray = $post->toArray();
                // Check if party list has been managed (post is linked to a party list)
                $postArray['party_list_managed'] = $post->partyListMember !== null;
                return $postArray;
            });
    }

    /**
     * Get approved posts (for public display)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getApprovedPosts()
    {
        return Post::where('status', 'approved')
            ->with('user:id,name,email')
            ->orderBy('approved_at', 'desc')
            ->get();
    }

    /**
     * Get user's posts
     *
     * @param string $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserPosts(string $userId)
    {
        return Post::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Approve a post
     * Automatically creates or adds to party list if party is specified
     *
     * @param int $postId
     * @return Post
     */
    public function approvePost(int $postId): Post
    {
        $updatedPost = DB::transaction(function () use ($postId) {
            $post = Post::findOrFail($postId);

            // Check if post has a party list and hasn't been linked yet
            if ($post->party && !$post->partyListMember) {
                // Try to find existing party list by name
                $existingPartyList = PartyList::where('name', $post->party)
                    ->where('is_active', true)
                    ->first();

                if ($existingPartyList) {
                    // Add to existing party list
                    $maxOrder = PartyListMember::where('party_list_id', $existingPartyList->id)
                        ->max('position_order') ?? 0;

                    PartyListMember::create([
                        'party_list_id' => $existingPartyList->id,
                        'post_id' => $post->id,
                        'position_order' => $maxOrder + 1,
                    ]);

                    $existingPartyList->increment('member_count');
                } else {
                    // Create new party list
                    $platform = $post->platform;
                    $platformArray = [];
                    if ($platform) {
                        if (is_string($platform)) {
                            // Try to parse if it's JSON string, otherwise treat as single item
                            $decoded = json_decode($platform, true);
                            $platformArray = $decoded ? (is_array($decoded) ? $decoded : [$decoded]) : [$platform];
                        } elseif (is_array($platform)) {
                            $platformArray = $platform;
                        }
                    }

                    $partyList = PartyList::create([
                        'name' => $post->party,
                        'description' => null,
                        'sector' => null,
                        'platform' => $platformArray,
                        'member_count' => 1,
                        'is_active' => true,
                    ]);

                    // Add post as first member
                    PartyListMember::create([
                        'party_list_id' => $partyList->id,
                        'post_id' => $post->id,
                        'position_order' => 1,
                    ]);
                }
            }

            // Update post status
            $post->update([
                'status' => 'approved',
                'approved_at' => now(),
                'rejected_at' => null,
                'admin_notes' => null,
            ]);

            return $post->fresh();
        });
        
        return $updatedPost;
    }

    /**
     * Reject a post
     *
     * @param int $postId
     * @param string|null $notes
     * @return Post
     */
    public function rejectPost(int $postId, ?string $notes = null): Post
    {
        $post = Post::findOrFail($postId);

        $post->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'approved_at' => null,
            'admin_notes' => $notes ?? 'Post rejected by admin',
        ]);

        $updatedPost = $post->fresh();
        
        return $updatedPost;
    }

    /**
     * Update a post (only allowed for rejected posts)
     *
     * @param int $postId
     * @param array $data
     * @param string $userId
     * @return Post
     */
    public function updatePost(int $postId, array $data, string $userId): Post
    {
        $post = Post::findOrFail($postId);

        // Only allow updates if:
        // 1. User owns the post
        // 2. Post is rejected (can't edit approved posts)
        if ($post->user_id !== $userId) {
            throw new \Exception('You can only edit your own posts');
        }

        if ($post->status === 'approved') {
            throw new \Exception('Cannot edit approved posts');
        }

        return DB::transaction(function () use ($post, $data) {
            // Process images if they exist
            $processedImages = null;
            if (isset($data['images']) && is_array($data['images']) && count($data['images']) > 0) {
                $processedImages = [];
                foreach ($data['images'] as $image) {
                    $processedImages[] = [
                        'url' => $image['file'] ?? null,
                        'caption' => $image['caption'] ?? '',
                    ];
                }
            }

            // Reset status to pending after update
            $post->update([
                'name' => $data['name'],
                'level' => $data['level'],
                'position' => $data['position'],
                'bio' => $data['bio'],
                'platform' => $data['platform'] ?? null,
                'education' => $data['education'] ?? null,
                'achievements' => $data['achievements'] ?? null,
                'images' => $processedImages,
                'profile_photo' => $data['profile_photo'] ?? $post->profile_photo,
                'party' => $data['party'] ?? null,
                'city_id' => $data['city_id'] ?? null,
                'district_id' => $data['district_id'] ?? null,
                'barangay_id' => $data['barangay_id'] ?? null,
                'status' => 'pending', // Reset to pending for re-review
                'admin_notes' => null, // Clear admin notes
                'rejected_at' => null,
        ]);

        return $post->fresh();
        });
    }

    /**
     * Get user notifications (status changes on their posts)
     *
     * @param string $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserNotifications(string $userId)
    {
        return Post::where('user_id', $userId)
            ->where('status', '!=', 'pending')
            ->orderBy('updated_at', 'desc')
            ->take(20)
            ->get(['id', 'name', 'status', 'admin_notes', 'approved_at', 'rejected_at', 'updated_at']);
    }
}
