<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'type'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Verifica se a role pode acessar um recurso específico
     */
    public function canAccess(string $resource): bool
    {
        return in_array($resource, $this->getAllowedResources());
    }

    /**
     * Retorna todos os recursos que essa role pode acessar
     * Ordenados por contexto de uso: Dashboard > Atendimento > Gestão > Financeiro
     */
    public function getAllowedResources(): array
    {
        return match ($this->type) {
            'ADMIN' => [
                'dashboard',
                'appointments',
                'patients',
                'medical-records',
                'medical-certificates',
                'services',
                'rooms',
                'users',
                'inventory',
                'financial',
                'marketing',
                'billing',
                'ai-credits',
                'settings'
            ],
            'RECEPTIONIST' => [
                'dashboard',
                'appointments',
                'patients',
                'services',
                'rooms',
            ],
            'DOCTOR' => [
                'dashboard',
                'appointments',
                'patients',
                'medical-records',
                'medical-certificates',
                'services',
                'rooms',
            ],
            'FINANCE' => [
                'dashboard',
                'inventory',
                'financial',
                'billing',
                'ai-credits'
            ],
            default => ['dashboard']
        };
    }

    /**
     * Verifica se é administrador
     */
    public function isAdmin(): bool
    {
        return $this->type === 'ADMIN';
    }
}
