<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'id_company',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
