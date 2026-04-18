<?php

namespace App\Enums;

enum OrgMemberRole: string
{
    case Owner   = 'owner';
    case Admin   = 'admin';
    case Manager = 'manager';
    case Member  = 'member';

    /** Todos los valores como array de strings. */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Regla de validación lista para usar en Form Requests. */
    public static function rule(): string
    {
        return 'in:' . implode(',', self::values());
    }

    public function label(): string
    {
        return match($this) {
            self::Owner   => 'Owner',
            self::Admin   => 'Admin',
            self::Manager => 'Manager',
            self::Member  => 'Member',
        };
    }
}
