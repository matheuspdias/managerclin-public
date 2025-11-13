<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'id_user',
        'id_customer',
        'id_room',
        'id_service',
        'date',
        'start_time',
        'end_time',
        'price',
        'status',
        'notes',
        'id_company',
        'created_by',
        'updated_by',
        'deleted_by',
    ];



    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'id_room');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'id_service');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'id_company');
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
