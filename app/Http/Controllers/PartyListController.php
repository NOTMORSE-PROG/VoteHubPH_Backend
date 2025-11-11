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
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([]);
        }

        $partyLists = PartyList::where('name', 'LIKE', "%{$query}%")
            ->orWhere('acronym', 'LIKE', "%{$query}%")
            ->where('is_active', true)
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
        $partyLists = PartyList::withCount('members')
            ->orderBy('name')
            ->get();

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
            ->findOrFail($id);

        return response()->json($partyList);
    }
}

