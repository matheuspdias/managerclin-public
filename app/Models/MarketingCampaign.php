<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingCampaign extends BaseModel
{
    protected $table = 'marketing_campaigns';

    protected $fillable = [
        'id_company',
        'name',
        'message',
        'media_type',
        'media_url',
        'media_filename',
        'local_media_path',
        'status',
        'target_audience',
        'target_filters',
        'scheduled_at',
        'sent_at',
        'total_recipients',
        'sent_count',
        'failed_count',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'target_filters' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'id_company');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(MarketingCampaignRecipient::class, 'id_campaign');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Escopos
    |--------------------------------------------------------------------------
    */

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopePendingToSend($query)
    {
        return $query->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now());
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos Auxiliares
    |--------------------------------------------------------------------------
    */

    public function canBeSent(): bool
    {
        return in_array($this->status, ['draft', 'scheduled']);
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'scheduled']);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['scheduled', 'sending']);
    }

    public function getSuccessRate(): float
    {
        if ($this->total_recipients === 0) {
            return 0;
        }

        return round(($this->sent_count / $this->total_recipients) * 100, 2);
    }
}
