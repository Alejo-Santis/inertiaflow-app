<?php

namespace App\Models;

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
    ];

    // Jerarquía dentro de un departamento tecnológico
    const ROLE_TEAM_LEAD = 'team_lead'; // Líder del equipo (gestión, punto de contacto)
    const ROLE_TECH_LEAD = 'tech_lead'; // Referente técnico (decisiones de arquitectura)
    const ROLE_SENIOR    = 'senior';    // Senior developer
    const ROLE_MEMBER    = 'member';    // Developer / miembro base

    public static function roles(): array
    {
        return [
            self::ROLE_TEAM_LEAD,
            self::ROLE_TECH_LEAD,
            self::ROLE_SENIOR,
            self::ROLE_MEMBER,
        ];
    }

    public static function roleLabel(string $role): string
    {
        return match ($role) {
            self::ROLE_TEAM_LEAD => 'Team Lead',
            self::ROLE_TECH_LEAD => 'Tech Lead',
            self::ROLE_SENIOR    => 'Senior',
            self::ROLE_MEMBER    => 'Member',
            default              => $role,
        };
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
