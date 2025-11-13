<?php

namespace App\DTO\Schedule;

class UpdateScheduleDTO
{
    public function __construct(
        public ?int $id,
        public int $id_user,
        public int $day_of_week,
        public string $start_time,
        public string $end_time,
        public bool $is_work = true,
        public ?int $id_company = null, // precisa deixar esse campo, pois precisa ser setado quando cria o usuario padrão
    ) {}


    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            id_user: $data['id_user'] ?? 0, // Default to 0 if not provided
            day_of_week: (int) $data['day_of_week'],
            start_time: $data['start_time'],
            end_time: $data['end_time'],
            is_work: $data['is_work'] ?? true,
            id_company: $data['id_company'] ?? null,
        );
    }
}
