<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'id_user',
        'id_customer',
        'id_appointment',
        'chief_complaint',
        'physical_exam',
        'diagnosis',
        'treatment_plan',
        'prescriptions',
        'observations',
        'follow_up_date',
        'medical_history',
        'allergies',
        'medications',
        'id_company',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'id_appointment');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'id_company');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
