<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The table associated with the model.
     */
    protected $table = 'User';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the primary key ID.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'email',
        'name',
        'password',
        'email_verified_at',
        'image',
        'provider',
        'provider_id',
        'language',
        'profile_completed',
        'prefer_anonymous_voting',
        'mute_comment_notifications',
        'mute_like_notifications',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'profile_completed' => 'boolean',
            'is_admin' => 'boolean',
            'prefer_anonymous_voting' => 'boolean',
            'mute_comment_notifications' => 'boolean',
            'mute_like_notifications' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The name of the "created at" column.
     */
    const CREATED_AT = 'createdAt';

    /**
     * The name of the "updated at" column.
     */
    const UPDATED_AT = 'updatedAt';

    /**
     * Relationships
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
