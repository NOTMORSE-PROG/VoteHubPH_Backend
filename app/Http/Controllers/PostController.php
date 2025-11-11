<?php

namespace App\Http\Controllers;

use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    protected PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * Create a new post (user)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|string|in:National,Local (City/Municipality),Barangay',
            'position' => 'required|string|max:255',
            'bio' => 'required|string|max:500',
            'platform' => 'nullable|string|max:1000',
            'education' => 'nullable|array',
            'education.*.level' => 'required|string',
            'education.*.school' => 'required|string',
            'achievements' => 'nullable|array',
            'achievements.*' => 'string',
            'images' => 'nullable|array',
            'images.*.file' => 'nullable|string', // Cloudinary URL
            'images.*.caption' => 'nullable|string|max:300',
            'profile_photo' => 'nullable|string', // Cloudinary URL
            'party' => 'nullable|string|max:255',
            'city_id' => 'nullable|integer|exists:cities,id',
            'district_id' => 'nullable|integer|exists:cities,id',
            'barangay_id' => 'nullable|integer|exists:barangays,id',
        ]);

        $userId = $request->get('authenticated_user_id');
        $post = $this->postService->createPost($validated, $userId);

        return response()->json([
            'message' => 'Post created successfully and pending admin approval',
            'post' => $post,
        ], 201);
    }

    /**
     * Get user's own posts
     */
    public function getUserPosts(Request $request)
    {
        $userId = $request->get('authenticated_user_id');
        $posts = $this->postService->getUserPosts($userId);
        return response()->json($posts);
    }

    /**
     * Get approved posts (public)
     */
    public function getApprovedPosts()
    {
        try {
            // Optimized: Use withCount to avoid N+1 queries
            $posts = \App\Models\Post::where('status', 'approved')
                ->with('user:id,name,email')
                ->withCount(['votes', 'comments'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($post) {
                    return [
                        'id' => $post->id,
                        'user_id' => $post->user_id,
                        'name' => $post->name,
                        'level' => $post->level,
                        'position' => $post->position,
                        'bio' => $post->bio,
                        'platform' => $post->platform,
                        'education' => $post->education,
                        'achievements' => $post->achievements,
                        'images' => $post->images,
                        'profile_photo' => $post->profile_photo,
                        'party' => $post->party,
                        'status' => $post->status,
                        'city_id' => $post->city_id,
                        'district_id' => $post->district_id,
                        'barangay_id' => $post->barangay_id,
                        'created_at' => $post->created_at,
                        'updated_at' => $post->updated_at,
                        'user' => $post->user,
                        'votes_count' => $post->votes_count ?? 0,
                        'comments_count' => $post->comments_count ?? 0,
                    ];
                });

            return response()->json($posts)
                ->header('Cache-Control', 'public, max-age=30'); // Cache for 30 seconds
        } catch (\Exception $e) {
            \Log::error('Error fetching approved posts: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to fetch posts',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single post with all related data (optimized - reduces API calls)
     */
    public function show(Request $request, $id)
    {
        // Try to get user ID from authenticated request or session
        $userId = $request->get('authenticated_user_id') ?? $this->getUserIdFromSession($request);

        // Get post with user in one query
        $post = \App\Models\Post::where('id', $id)
            ->where('status', 'approved')
            ->with('user:id,name,email')
            ->firstOrFail();

        // Get vote data
        $votesCount = \App\Models\Vote::where('post_id', $id)->count();
        $userVote = $userId ? \App\Models\Vote::where('post_id', $id)
            ->where('user_id', $userId)
            ->first() : null;

        // Get all comment IDs for this post to check likes in one query
        $allCommentIds = \App\Models\Comment::where('post_id', $id)->pluck('id')->toArray();
        
        // Get all comment likes for this user in one query
        $userLikedCommentIds = $userId ? \App\Models\CommentLike::whereIn('comment_id', $allCommentIds)
            ->where('user_id', $userId)
            ->pluck('comment_id')
            ->toArray() : [];

        // Get comments with replies (optimized eager loading - only load necessary relationships)
        $comments = \App\Models\Comment::where('post_id', $id)
            ->whereNull('parent_id')
            ->with([
                'user:id,name', // Only load user id and name
                'replies' => function ($query) {
                    $query->with('user:id,name')
                          ->orderBy('created_at', 'asc');
                },
                'replies.replies' => function ($query) {
                    $query->with('user:id,name')
                          ->orderBy('created_at', 'asc');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($comment) use ($userId, $userLikedCommentIds) {
                return $this->formatCommentForDisplay($comment, $userId, $userLikedCommentIds);
            });

        $commentsCount = \App\Models\Comment::where('post_id', $id)->count();

        return response()->json([
            'post' => $post,
            'votes_count' => $votesCount,
            'comments_count' => $commentsCount,
            'user_has_voted' => $userVote !== null,
            'user_vote_is_anonymous' => $userVote ? $userVote->is_anonymous : null,
            'comments' => $comments,
        ]);
    }

    private function formatCommentForDisplay($comment, $userId, $userLikedCommentIds = [])
    {
        $formatted = [
            'id' => $comment->id,
            'post_id' => $comment->post_id,
            'parent_id' => $comment->parent_id,
            'user_id' => $comment->user_id,
            'user_name' => $comment->is_anonymous ? 'Anonymous' : $comment->user->name,
            'content' => $comment->content,
            'is_anonymous' => $comment->is_anonymous,
            'likes_count' => $comment->likes_count,
            'created_at' => $comment->created_at,
            'user_has_liked' => in_array($comment->id, $userLikedCommentIds),
            'replies' => [],
        ];

        if ($comment->relationLoaded('replies')) {
            $formatted['replies'] = $comment->replies->map(function ($reply) use ($userId, $userLikedCommentIds) {
                return $this->formatCommentForDisplay($reply, $userId, $userLikedCommentIds);
            })->toArray();
        }

        return $formatted;
    }

    /**
     * Optionally get user ID from session (for public routes)
     */
    private function getUserIdFromSession($request)
    {
        try {
            $cookies = $request->cookies->all();
            $sessionToken = $cookies['next-auth.session-token']
                         ?? $cookies['__Secure-next-auth.session-token']
                         ?? $cookies['next-auth_session-token']
                         ?? $cookies['__Host-next-auth.session-token']
                         ?? null;

            if (!$sessionToken) {
                return null;
            }

            $session = \DB::table('Session')
                ->where('sessionToken', $sessionToken)
                ->where('expires', '>', now())
                ->first();

            return $session ? $session->userId : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get all pending posts (admin only)
     */
    public function getPendingPosts()
    {
        // TODO: Add admin middleware check
        $posts = $this->postService->getPendingPosts();
        return response()->json($posts);
    }

    /**
     * Get all posts (admin only)
     */
    public function getAllPosts()
    {
        // TODO: Add admin middleware check
        $posts = $this->postService->getAllPosts();
        return response()->json($posts);
    }

    /**
     * Approve a post (admin only)
     */
    public function approve(Request $request, $id)
    {
        // TODO: Add admin middleware check
        $post = $this->postService->approvePost($id);

        return response()->json([
            'message' => 'Post approved successfully',
            'post' => $post,
        ]);
    }

    /**
     * Reject a post (admin only)
     */
    public function reject(Request $request, $id)
    {
        // TODO: Add admin middleware check
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $post = $this->postService->rejectPost($id, $validated['admin_notes'] ?? null);

        return response()->json([
            'message' => 'Post rejected',
            'post' => $post,
        ]);
    }

    /**
     * Update a post (only for rejected posts)
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|string|in:National,Local (City/Municipality),Barangay',
            'position' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    // Reject "Party-List Representative" as it's not a valid position
                    if (strtolower(trim($value)) === 'party-list representative' || strtolower(trim($value)) === 'party list representative') {
                        $fail('Party-List Representative is not a valid position. Please select a valid position.');
                    }
                },
            ],
            'bio' => 'required|string|max:500',
            'platform' => 'nullable|string|max:1000',
            'education' => 'nullable|array',
            'education.*.level' => 'required|string',
            'education.*.school' => 'required|string',
            'achievements' => 'nullable|array',
            'achievements.*' => 'string',
            'images' => 'nullable|array',
            'images.*.file' => 'nullable|string',
            'images.*.caption' => 'nullable|string|max:300',
            'profile_photo' => 'nullable|string',
            'party' => 'nullable|string|max:255',
            'city_id' => 'nullable|integer|exists:cities,id',
            'district_id' => 'nullable|integer|exists:cities,id',
            'barangay_id' => 'nullable|integer|exists:barangays,id',
        ]);

        $userId = $request->get('authenticated_user_id');

        try {
            $post = $this->postService->updatePost($id, $validated, $userId);
            return response()->json([
                'message' => 'Post updated successfully and pending admin approval',
                'post' => $post,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Get user notifications (status changes on their posts)
     */
    public function getNotifications(Request $request)
    {
        $userId = $request->get('authenticated_user_id');
        $posts = $this->postService->getUserNotifications($userId);
        return response()->json($posts);
    }
}
