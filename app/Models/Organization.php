<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Organization extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'owner_id',
        'nit',
        'dv',
        'name',
        'slug',
        'description',
        'color',
        'logo',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_members')
            ->withPivot('role', 'joined_at');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(OrganizationInvitation::class);
    }

    public function pendingInvitations(): HasMany
    {
        return $this->hasMany(OrganizationInvitation::class)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now());
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
