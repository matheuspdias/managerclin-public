<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSchedule extends BaseModel
{
    use SoftDeletes;

    protected $table = 'user_schedules';

    protected $fillable = [
        'id_user',
        'date',
        'day_of_week',
        'start_time',
        'end_time',
        'is_work',
        'id_company',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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
