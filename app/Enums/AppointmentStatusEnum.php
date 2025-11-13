<?php

namespace App\Enums;

class AppointmentStatusEnum
{
    const SCHEDULED = 'SCHEDULED';
    const IN_PROGRESS = 'IN_PROGRESS';
    const COMPLETED = 'COMPLETED';
    const CANCELLED = 'CANCELLED';

    public static function getValues(): array
    {
        return [
            self::SCHEDULED,
            self::IN_PROGRESS,
            self::COMPLETED,
            self::CANCELLED,
        ];
    }
}
