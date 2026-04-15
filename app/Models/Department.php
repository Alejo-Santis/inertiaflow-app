<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'lead_id',
        'name',
        'description',
        'color',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(DepartmentMember::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'department_members')
            ->withPivot('role', 'joined_at');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
