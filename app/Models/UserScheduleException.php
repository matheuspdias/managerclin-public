<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserScheduleException extends BaseModel
{
    use SoftDeletes;

    protected $table = 'user_schedule_exceptions';

    protected $fillable = [
        'id_user',
        'date',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
        'reason',
        'id_company',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
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
