<?php

namespace App\Enums;

enum DeptMemberRole: string
{
    case TeamLead = 'team_lead';
    case TechLead = 'tech_lead';
    case Senior   = 'senior';
    case Member   = 'member';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function rule(): string
    {
        return 'in:' . implode(',', self::values());
    }

    public function label(): string
    {
        return match ($this) {
            self::TeamLead => 'Team Lead',
            self::TechLead => 'Tech Lead',
            self::Senior   => 'Senior',
            self::Member   => 'Member',
        };
    }
}
