<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'nim', 'name', 'session_id',
        'ip_address', 'user_agent', 'is_active',
    ];

    protected $casts = [
        'login_time'    => 'datetime',
        'last_activity' => 'datetime',
        'is_active'     => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
                     ->where('last_activity', '>=', now()->subMinutes(30));
    }
}
