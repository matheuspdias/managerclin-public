<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Customer extends BaseModel
{
    use SoftDeletes, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'birthdate',
        'cpf',
        'notes',
        'image',
        'id_company',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = [
        'birthdate',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'id_company');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'id_customer');
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class, 'id_customer');
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'id_customer');
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
