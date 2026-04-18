<?php

namespace App\Models;

use App\Enums\OrgMemberRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationMember extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'organization_id',
        'user_id',
        'role',
        'joined_at',
    ];

    protected $casts = [
        'role'      => OrgMemberRole::class,
        'joined_at' => 'datetime',
    ];

    /** @return string[] */
    public static function roles(): array
    {
        return OrgMemberRole::values();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
