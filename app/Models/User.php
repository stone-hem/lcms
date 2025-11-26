<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'first_name',
        'middle_name',
        'last_name',
        'calling_code',
        'phone',
        'can_login',
        'meta',
        'location',
        'date_of_birth',
        'profile_photo_path',
        'is_active',
        'is_external_counsel',
        'email_verified_at',
        'phone_verified_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'date_of_birth' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'meta' => 'array',           // or 'json' if you prefer
            'location' => 'array',
            'can_login' => 'boolean',
            'is_active' => 'boolean',
            'is_external_counsel' => 'boolean',
            'password' => 'hashed',
        ];
    }

    // Relationships

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function externalFirm()
    {
        return $this->hasOne(ExternalFirm::class);
    }

    // Optional: Scope for active users
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    // Optional: Check if user can login
    public function canLogin()
    {
        return $this->can_login && $this->is_active;
    }
}