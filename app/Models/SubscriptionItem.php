<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionItem extends Model
{
    protected $table = 'subscription_items';

    protected $fillable = [
        'subscription_id',
        'stripe_id',
        'stripe_product',
        'stripe_price',
        'quantity',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }
}
