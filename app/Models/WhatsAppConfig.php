<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppConfig extends BaseModel
{
    use SoftDeletes;

    protected $table = 'whatsapp_configs';

    protected $fillable = [
        'id_company',
        'instance_name',
        'instance_id',
        'token',
        'is_active',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // auditoria
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
