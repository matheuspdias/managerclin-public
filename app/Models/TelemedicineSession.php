<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Model para gerenciar sessões de telemedicina
 *
 * @property int $id
 * @property int $appointment_id
 * @property string $room_name
 * @property string $server_url
 * @property string $status
 * @property Carbon|null $started_at
 * @property Carbon|null $ended_at
 * @property int $duration_minutes
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TelemedicineSession extends Model
{
    /**
     * A tabela associada ao model
     */
    protected $table = 'telemedicine_sessions';

    /**
     * Os atributos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'appointment_id',
        'room_name',
        'server_url',
        'status',
        'started_at',
        'ended_at',
        'duration_minutes',
        'credits_consumed',
        'last_credit_check_at',
        'notes',
    ];

    /**
     * Os atributos que devem ser convertidos
     */
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_minutes' => 'integer',
        'credits_consumed' => 'integer',
        'last_credit_check_at' => 'datetime',
    ];

    /**
     * Status possíveis da sessão
     */
    public const STATUS_WAITING = 'WAITING';
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CANCELLED = 'CANCELLED';

    /**
     * Relacionamento com Appointment
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * Scope para buscar sessões por status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para buscar sessões ativas
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope para buscar sessões em espera
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', self::STATUS_WAITING);
    }

    /**
     * Scope para buscar sessões completadas
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Accessor para calcular duração automaticamente
     */
    public function getCalculatedDurationAttribute(): int
    {
        if ($this->started_at && $this->ended_at) {
            return $this->started_at->diffInMinutes($this->ended_at);
        }

        return 0;
    }

    /**
     * Accessor para URL de entrada na sala Jitsi
     * Para JaaS (8x8), o formato é: https://8x8.vc/vpaas-magic-cookie-xxx/room-name
     * Para Jitsi público ou custom, o formato é: https://meet.jit.si/room-name
     */
    public function getJoinUrlAttribute(): string
    {
        $provider = config('telemedicine.provider', 'jitsi');
        $baseUrl = rtrim($this->server_url, '/');

        // Se for JaaS, incluir o App ID no path
        if ($provider === 'jaas') {
            $appId = config('telemedicine.jaas_app_id');
            if ($appId) {
                return $baseUrl . '/' . $appId . '/' . $this->room_name;
            }
        }

        // Jitsi público ou custom
        return $baseUrl . '/' . $this->room_name;
    }

    /**
     * Verifica se a sessão está ativa
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Verifica se a sessão está em espera
     */
    public function isWaiting(): bool
    {
        return $this->status === self::STATUS_WAITING;
    }

    /**
     * Verifica se a sessão está completada
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Verifica se a sessão foi cancelada
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Inicia a sessão
     */
    public function start(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'started_at' => now(),
        ]);
    }

    /**
     * Finaliza a sessão
     */
    public function complete(?string $notes = null): void
    {
        $endedAt = now();
        $durationMinutes = $this->started_at
            ? $this->started_at->diffInMinutes($endedAt)
            : 0;

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'ended_at' => $endedAt,
            'duration_minutes' => $durationMinutes,
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Cancela a sessão
     */
    public function cancel(?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'ended_at' => now(),
            'notes' => $notes ?? $this->notes,
        ]);
    }
}
