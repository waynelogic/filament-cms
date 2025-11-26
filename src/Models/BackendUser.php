<?php

namespace Waynelogic\FilamentCms\Models;

use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class BackendUser extends Authenticatable implements FilamentUser, HasMedia, HasAvatar
{
    use Notifiable, InteractsWithMedia;

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'is_super_admin',
        'password',
        'settings',
        'last_name'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_super_admin' => 'boolean',
        'password' => 'hashed',
        'settings' => 'array',
    ];
    public function registerMediaCollections(): void
    {
        $this->addmediaCollection('admin_avatar')->singleFile();
    }
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('admin_avatar');
    }

    public function getFullNameAttribute(): string
    {
        return implode(' ', array_filter([$this->name, $this->last_name]));
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->getFirstMediaUrl('admin_avatar');
    }
}
