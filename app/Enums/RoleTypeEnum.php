<?php

namespace App\Enums;

class RoleTypeEnum
{
    const ADMIN = 'ADMIN';
    const DOCTOR = 'DOCTOR';
    const RECEPTIONIST = 'RECEPTIONIST';
    const FINANCE = 'FINANCE';

    public static function getValues(): array
    {
        return [
            self::ADMIN,
            self::DOCTOR,
            self::RECEPTIONIST,
            self::FINANCE,
        ];
    }
}
