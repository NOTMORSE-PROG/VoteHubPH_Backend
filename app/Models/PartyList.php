<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartyList extends Model
{
    protected $fillable = [
        'name',
        'acronym',
        'description',
        'sector',
        'platform',
        'logo_url',
        'website',
        'email',
        'social_media',
        'votes',
        'member_count',
        'is_active',
    ];

    protected $casts = [
        'platform' => 'array',
        'social_media' => 'array',
        'is_active' => 'boolean',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(PartyListMember::class)->orderBy('position_order');
    }
}

