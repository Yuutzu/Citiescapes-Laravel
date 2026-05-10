<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int         $id
 * @property int         $tenant_id
 * @property int         $contract_id
 * @property int         $room_id
 * @property string      $type
 * @property string|null $billing_period
 * @property string      $base_rent
 * @property string      $utilities
 * @property string      $electricity
 * @property string      $water
 * @property string      $wifi
 * @property string      $deposit_amount
 * @property string      $room_key_fee
 * @property string      $penalty_amount
 * @property string      $total_amount
 * @property \Illuminate\Support\Carbon      $due_date
 * @property string      $status
 * @property int         $days_overdue
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property string      $status_badge
 */
class Bill extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'contract_id',
        'room_id',
        'type',
        'billing_period',
        'base_rent',
        'utilities',
        'electricity',
        'water',
        'wifi',
        'deposit_amount',
        'room_key_fee',
        'penalty_amount',
        'total_amount',
        'due_date',
        'status',
        'days_overdue',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date'       => 'date',
            'paid_at'        => 'datetime',
            'base_rent'      => 'decimal:2',
            'utilities'      => 'decimal:2',
            'electricity'    => 'decimal:2',
            'water'          => 'decimal:2',
            'wifi'           => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'room_key_fee'   => 'decimal:2',
            'penalty_amount' => 'decimal:2',
            'total_amount'   => 'decimal:2',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function penaltyOverrides()
    {
        return $this->hasMany(PenaltyOverride::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'bg-green-100 text-green-800',
            'unpaid' => 'bg-gray-100 text-gray-800',
            'grace' => 'bg-amber-100 text-amber-800',
            'overdue' => 'bg-orange-100 text-orange-800',
            'delinquent' => 'bg-red-100 text-red-800',
            'eviction' => 'bg-red-200 text-red-900',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function scopeUnpaid($q)
    {
        return $q->whereNotIn('status', ['paid', 'archived']);
    }
    public function scopeForPeriod($q, string $period)
    {
        return $q->where('billing_period', $period);
    }
}
