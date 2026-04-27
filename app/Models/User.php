<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'full_name', 'email', 'contact_number', 'address',
        'emergency_contact', 'password', 'role', 'status',
        'must_change_password', 'activated_at', 'archived_at',
        'archived_by', 'failed_login_attempts', 'locked_until',
        'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'activated_at'      => 'datetime',
            'archived_at'       => 'datetime',
            'locked_until'      => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    /* ── Role helpers ─────────────────────────── */
    public function isGm(): bool     { return $this->role === 'gm'; }
    public function isTenant(): bool { return $this->role === 'tenant'; }

    public function isActive(): bool     { return $this->status === 'active'; }
    public function isLocked(): bool     { return $this->status === 'locked' && $this->locked_until?->isFuture(); }
    public function isArchived(): bool   { return $this->status === 'archived'; }

    /* ── Relationships ────────────────────────── */
    public function contracts()
    {
        return $this->hasMany(Contract::class, 'tenant_id');
    }

    public function activeContract()
    {
        return $this->hasOne(Contract::class, 'tenant_id')->where('status', 'active')->latestOfMany();
    }

    public function bills()
    {
        return $this->hasMany(Bill::class, 'tenant_id');
    }

    public function room()
    {
        return $this->hasOne(Room::class, 'current_tenant_id');
    }

    public function notifications()
    {
        return $this->hasMany(NotificationLog::class, 'user_id');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    public function otpRecords()
    {
        return $this->hasMany(OtpRecord::class, 'user_id');
    }

    /* ── Lockout logic ────────────────────────── */
    public function incrementFailedLogin(): void
    {
        $this->increment('failed_login_attempts');
        $threshold = config('citiescapes.auth.lockout_threshold', 5);

        if ($this->failed_login_attempts >= $threshold) {
            $this->update([
                'status'       => 'locked',
                'locked_until' => now()->addMinutes(config('citiescapes.auth.lockout_minutes', 15)),
            ]);
        }
    }

    public function resetFailedLogin(): void
    {
        $this->update(['failed_login_attempts' => 0]);
    }

    public function autoUnlockIfExpired(): bool
    {
        if ($this->status === 'locked' && $this->locked_until?->isPast()) {
            $this->update([
                'status'               => 'active',
                'locked_until'         => null,
                'failed_login_attempts'=> 0,
            ]);
            return true;
        }
        return false;
    }
}
