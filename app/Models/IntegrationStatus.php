<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IntegrationStatus extends Model
{
    use HasFactory;

    protected $table = 'integration_status';

    protected $fillable = [
        'company_id',
        'service',
        'status',
        'message',
    ];

    // Relação com a empresa
    public function company()
    {
        return $this->belongsTo(Company::class, 'id_company');
    }
}
