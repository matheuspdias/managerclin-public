<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\RoleTypeEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'id_role',
        'name',
        'email',
        'phone',
        'crm',
        'password',
        'image',
        'id_company',
        'is_owner',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_owner' => 'boolean',
    ];

    protected $appends = ['image_url'];

    public static function forCurrentCompany()
    {
        return static::where('id_company', Auth::user()->id_company);
    }


    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_role');
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'id_user');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'id_company');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'id_user');
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

    public function isAdmin(): bool
    {
        return $this->role && $this->role->type === RoleTypeEnum::ADMIN;
    }

    public function isDoctor(): bool
    {
        return $this->role && $this->role->type === RoleTypeEnum::DOCTOR;
    }

    public function isReceptionist(): bool
    {
        return $this->role && $this->role->type === RoleTypeEnum::RECEPTIONIST;
    }

    public function isFinance(): bool
    {
        return $this->role && $this->role->type === RoleTypeEnum::FINANCE;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
