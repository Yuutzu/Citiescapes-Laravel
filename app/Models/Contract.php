<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'room_id', 'base_rent_rate', 'deposit',
        'first_month_rent', 'room_key_fee', 'requested_amenities',
        'start_date', 'end_date', 'status',
        'penalty_rate', 'penalty_grace_days', 'house_rules',
        'penalty_schedule', 'scan_file_path', 'scan_file_name',
        'step1_acknowledged_at', 'step2_acknowledged_at',
        'activated_at', 'terminated_at', 'termination_reason',
        'warning_30_sent', 'warning_7_sent', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date'              => 'date',
            'end_date'                => 'date',
            'base_rent_rate'          => 'decimal:2',
            'deposit'                 => 'decimal:2',
            'first_month_rent'        => 'decimal:2',
            'room_key_fee'            => 'decimal:2',
            'requested_amenities'     => 'array',
            'penalty_rate'            => 'decimal:2',
            'step1_acknowledged_at'   => 'datetime',
            'step2_acknowledged_at'   => 'datetime',
            'activated_at'            => 'datetime',
            'terminated_at'           => 'datetime',
            'warning_30_sent'         => 'boolean',
            'warning_7_sent'          => 'boolean',
        ];
    }

    /* ── Relationships ─────────────────── */
    public function tenant()         { return $this->belongsTo(User::class, 'tenant_id'); }
    public function room()           { return $this->belongsTo(Room::class); }
    public function bills()          { return $this->hasMany(Bill::class); }
    public function initialPayment() { return $this->hasOne(InitialPayment::class); }
    public function createdBy()      { return $this->belongsTo(User::class, 'created_by'); }

    /**
     * First month rent falls back to base rent rate when not set explicitly.
     */
    public function getEffectiveFirstMonthRentAttribute(): string
    {
        return $this->first_month_rent ?? $this->base_rent_rate;
    }

    /* ── Computed: timer ──────────────── */
    public function getDaysRemainingAttribute(): int
    {
        return max(0, (int) now()->startOfDay()->diffInDays($this->end_date, false));
    }

    public function getProgressPercentAttribute(): float
    {
        $total = $this->start_date->diffInDays($this->end_date) ?: 1;
        $elapsed = $this->start_date->diffInDays(now()->startOfDay());
        return round(min(100, ($elapsed / $total) * 100), 1);
    }

    /**
     * Green > 30 days, Amber ≤ 30, Red ≤ 7, Gray = Expired
     */
    public function getTimerBadgeAttribute(): string
    {
        if ($this->status !== 'active') return 'gray';
        $d = $this->days_remaining;
        if ($d <= 0) return 'gray';
        if ($d <= 7) return 'red';
        if ($d <= 30) return 'amber';
        return 'green';
    }

    public function getTimerBadgeCssAttribute(): string
    {
        return match($this->timer_badge) {
            'green' => 'bg-green-100 text-green-800',
            'amber' => 'bg-amber-100 text-amber-800',
            'red'   => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /* ── Scopes ────────────────────────── */
    public function scopeActive($q)  { return $q->where('status', 'active'); }
    public function scopeDraft($q)   { return $q->where('status', 'draft'); }
    public function scopeExpiring($q, int $days = 30) {
        return $q->active()->whereBetween('end_date', [now(), now()->addDays($days)]);
    }
}
