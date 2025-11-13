<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class MedicalCertificate extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'id_appointment',
        'id_user',
        'id_customer',
        'content',
        'days_off',
        'issue_date',
        'valid_until',
        'digital_signature',
        'validation_hash'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'valid_until' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($certificate) {
            $certificate->validation_hash = Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }
}
