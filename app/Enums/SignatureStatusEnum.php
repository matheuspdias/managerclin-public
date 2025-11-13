<?php

namespace App\Enums;

class SignatureStatusEnum
{
    // 'TRIAL', 'ACTIVE', 'EXPIRED', 'CANCELLED'
    const TRIAL = 'TRIAL';
    const ACTIVE = 'ACTIVE';
    const EXPIRED = 'EXPIRED';
    const CANCELLED = 'CANCELLED';

    public static function getValues(): array
    {
        return [
            self::TRIAL,
            self::ACTIVE,
            self::EXPIRED,
            self::CANCELLED,
        ];
    }
}
