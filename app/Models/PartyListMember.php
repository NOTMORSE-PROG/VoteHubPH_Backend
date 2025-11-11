<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartyListMember extends Model
{
    protected $fillable = [
        'party_list_id',
        'post_id',
        'position_order',
    ];

    public function partyList(): BelongsTo
    {
        return $this->belongsTo(PartyList::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}

