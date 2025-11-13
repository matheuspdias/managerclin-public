<?php

namespace App\DTO\Schedule;

class UpdateScheduleCollectionDTO
{
    /** @var UpdateScheduleDTO[] */
    public array $schedules;

    public function __construct(array $schedules)
    {
        $this->schedules = $schedules;
    }

    public static function makeFromRequest(array $data): self
    {
        return new self(
            array_map(fn($item) => UpdateScheduleDTO::fromArray($item), $data)
        );
    }
}
