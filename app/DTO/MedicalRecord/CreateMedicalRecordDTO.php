<?php

namespace App\DTO\MedicalRecord;

use App\Http\Requests\MedicalRecord\CreateMedicalRecordRequest;
use Illuminate\Support\Facades\Auth;

class CreateMedicalRecordDTO
{
    public function __construct(
        public int $id_customer,
        public int $id_user,
        public ?int $id_appointment = null,
        public string $chief_complaint = '',
        public string $physical_exam = '',
        public string $diagnosis = '',
        public string $treatment_plan = '',
        public string $prescriptions = '',
        public string $observations = '',
        public ?string $follow_up_date = null,
        public string $medical_history = '',
        public string $allergies = '',
        public string $medications = ''
    ) {}

    public static function makeFromRequest(CreateMedicalRecordRequest $request): self
    {
        $data = $request->validated();
        return new self(
            $data['id_customer'],
            Auth::id(),
            $data['id_appointment'] ?? null,
            $data['chief_complaint'],
            $data['physical_exam'],
            $data['diagnosis'],
            $data['treatment_plan'],
            $data['prescriptions'] ?? '',
            $data['observations'] ?? '',
            $data['follow_up_date'] ?? null,
            $data['medical_history'] ?? '',
            $data['allergies'] ?? '',
            $data['medications'] ?? ''
        );
    }
}
