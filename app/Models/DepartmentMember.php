<?php

namespace App\Models;

use App\Enums\DeptMemberRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentMember extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'department_id',
        'user_id',
        'role',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'role'      => DeptMemberRole::class,
    ];

    public static function roles(): array
    {
        return DeptMemberRole::values();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
