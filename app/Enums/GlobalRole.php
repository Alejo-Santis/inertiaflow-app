<?php

namespace App\Enums;

/**
 * Roles globales del sistema (gestionados por Spatie Permission).
 * Usar siempre estas constantes en lugar de strings literales.
 */
enum GlobalRole: string
{
    case Admin   = 'admin';
    case Manager = 'manager';
    case Member  = 'member';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
