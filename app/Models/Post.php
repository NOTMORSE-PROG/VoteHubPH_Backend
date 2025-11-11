<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'level',
        'position',
        'bio',
        'platform',
        'education',
        'achievements',
        'images',
        'profile_photo',
        'party',
        'status',
        'admin_notes',
        'approved_at',
        'rejected_at',
        'city_id',
        'district_id',
        'barangay_id',
    ];

    protected $casts = [
        'education' => 'array',
        'achievements' => 'array',
        'images' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function partyListMember(): HasOne
    {
        return $this->hasOne(PartyListMember::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
