<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = ['region_id', 'province_id', 'parent_city_id', 'name', 'type', 'is_district', 'psgc_code'];

    protected $casts = [
        'is_district' => 'boolean',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function parentCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'parent_city_id');
    }

    public function districts(): HasMany
    {
        return $this->hasMany(City::class, 'parent_city_id');
    }

    public function barangays(): HasMany
    {
        return $this->hasMany(Barangay::class);
    }
}
