<?php

namespace App\Http\Controllers;

use App\Models\PartyList;
use App\Models\PartyListMember;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartyListController extends Controller
{
    /**
     * Search party lists by name
     * For admin: shows all party lists
     * For public: shows only active party lists
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([]);
        }

        // Check if this is an admin request (has X-User-Id header)
        $isAdmin = $request->header('X-User-Id') !== null;
        
        // Case-insensitive search using LOWER() for cross-database compatibility
        $queryLower = strtolower($query);
        
        // Properly group the OR conditions so the search works correctly
        $partyListsQuery = PartyList::where(function ($q) use ($queryLower) {
            $q->whereRaw('LOWER(name) LIKE ?', ["%{$queryLower}%"])
              ->orWhereRaw('LOWER(acronym) LIKE ?', ["%{$queryLower}%"]);
        });

        // Only filter by is_active for public (non-admin) requests
        if (!$isAdmin) {
            $partyListsQuery->where('is_active', true);
        }

        $partyLists = $partyListsQuery
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'acronym', 'sector', 'member_count']);

        return response()->json($partyLists);
    }

    /**
     * Get all party lists (for admin)
     */
    public function index()
    {
        // Clean up empty party lists before fetching
        $this->cleanupEmptyPartyLists();
        
        $partyLists = PartyList::withCount('members')
            ->orderBy('name')
            ->get();

        return response()->json($partyLists);
    }

    /**
     * Get all active party lists (public - for users to view)
     */
    public function getPublicPartyLists()
    {
        // Clean up empty party lists before fetching
        $this->cleanupEmptyPartyLists();
        
        // Only return party lists that have at least one member
        $partyLists = PartyList::where('is_active', true)
            ->whereHas('members') // Only party lists with members
            ->withCount('members')
            ->orderBy('name')
            ->get(['id', 'name', 'acronym', 'description', 'sector', 'logo_url', 'member_count', 'created_at']);

        return response()->json($partyLists);
    }

    /**
     * Create a new party list
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:party_lists,name',
            'acronym' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'sector' => 'nullable|string|max:100',
            'platform' => 'nullable|array',
            'post_id' => 'required|exists:posts,id', // Post to add as first member
        ]);

        return DB::transaction(function () use ($validated) {
            $partyList = PartyList::create([
                'name' => $validated['name'],
                'acronym' => $validated['acronym'] ?? null,
                'description' => $validated['description'] ?? null,
                'sector' => $validated['sector'] ?? null,
                'platform' => $validated['platform'] ?? [],
                'member_count' => 1,
            ]);

            // Add the post as the first member
            PartyListMember::create([
                'party_list_id' => $partyList->id,
                'post_id' => $validated['post_id'],
                'position_order' => 1,
            ]);

            // Update post to link to party list (direct update without loading model)
            Post::where('id', $validated['post_id'])
                ->update(['party' => $partyList->name]);

            return response()->json([
                'message' => 'Party list created successfully',
                'party_list' => [
                    'id' => $partyList->id,
                    'name' => $partyList->name,
                    'acronym' => $partyList->acronym,
                    'member_count' => $partyList->member_count,
                ],
            ], 201);
        });
    }

    /**
     * Add a post to an existing party list
     */
    public function addMember(Request $request, $id)
    {
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,id',
        ]);

        $partyList = PartyList::findOrFail($id);

        // Check if post is already a member
        $existingMember = PartyListMember::where('party_list_id', $id)
            ->where('post_id', $validated['post_id'])
            ->first();

        if ($existingMember) {
            return response()->json([
                'message' => 'Post is already a member of this party list',
            ], 400);
        }

        return DB::transaction(function () use ($partyList, $validated) {
            // Get the next position order (optimized with select)
            $maxOrder = PartyListMember::where('party_list_id', $partyList->id)
                ->select('position_order')
                ->max('position_order') ?? 0;

            // Add the member
            PartyListMember::create([
                'party_list_id' => $partyList->id,
                'post_id' => $validated['post_id'],
                'position_order' => $maxOrder + 1,
            ]);

            // Update member count (increment without refreshing)
            $partyList->increment('member_count');

            // Update post to link to party list (only update party field)
            Post::where('id', $validated['post_id'])
                ->update(['party' => $partyList->name]);

            return response()->json([
                'message' => 'Member added successfully',
                'party_list' => [
                    'id' => $partyList->id,
                    'name' => $partyList->name,
                    'member_count' => $partyList->member_count + 1, // Already incremented
                ],
            ]);
        });
    }

    /**
     * Get a single party list with members
     */
    public function show($id)
    {
        $partyList = PartyList::with(['members.post.user:id,name,email'])
            ->withCount('members')
            ->findOrFail($id);

        // If party list has no members, delete it and return 404
        if ($partyList->members_count === 0) {
            $partyList->delete();
            abort(404, 'Party list not found');
        }

        return response()->json($partyList);
    }

    /**
     * Clean up party lists that have no members
     */
    private function cleanupEmptyPartyLists(): void
    {
        // Find all party lists with no members using whereDoesntHave
        $emptyPartyLists = PartyList::whereDoesntHave('members')->get();

        // Delete empty party lists
        foreach ($emptyPartyLists as $partyList) {
            $partyList->delete();
        }
    }
}

