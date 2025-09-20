<?php

namespace Waynelogic\FilamentCms\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    protected $table = 'sessions';

    protected $guarded = [
        'id',
        'user_id',
        'ip_address',
        'user_agent',
        'payload',
        'last_activity',
    ];

    protected $appends = ['expires_at'];

    protected $casts = [
        'id' => 'string',
        'last_activity' => 'timestamp',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(BackendUser::class);
    }

    public function isExpired(): bool
    {
        return $this->last_activity < Carbon::now()->subMinutes(config('session.lifetime'))->getTimestamp();
    }
    public function getIsExpiredAttribute(): bool
    {
        return $this->isExpired();
    }

    public function getExpiresAtAttribute(): string
    {
        return Carbon::createFromTimestamp($this->last_activity)->addMinutes(config('session.lifetime'))->toDateTimeString();
    }
}
