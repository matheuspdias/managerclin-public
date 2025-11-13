<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingCampaignRecipient extends Model
{
    protected $table = 'marketing_campaign_recipients';

    protected $fillable = [
        'id_campaign',
        'id_customer',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class, 'id_campaign');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
