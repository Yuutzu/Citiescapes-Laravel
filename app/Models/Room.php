<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'room_number', 'floor_level', 'room_type', 'photos', 'amenities',
        'rate', 'max_occupants', 'status', 'description',
        'current_tenant_id', 'last_updated_by', 'last_status_update',
    ];

    protected function casts(): array
    {
        return [
            'photos'             => 'array',
            'amenities'          => 'array',
            'rate'               => 'decimal:2',
            'last_status_update' => 'datetime',
        ];
    }

    /* ── Scopes ──────────────────────────── */
    public function scopeAvailable($q)          { return $q->where('status', 'available'); }
    public function scopeOccupied($q)           { return $q->where('status', 'occupied'); }
    public function scopeUnderMaintenance($q)   { return $q->where('status', 'under_maintenance'); }
    public function scopeOnFloor($q, int $f)    { return $q->where('floor_level', $f); }

    /* ── Relationships ───────────────────── */
    public function currentTenant()   { return $this->belongsTo(User::class, 'current_tenant_id'); }
    public function lastUpdatedBy()   { return $this->belongsTo(User::class, 'last_updated_by'); }
    public function contracts()       { return $this->hasMany(Contract::class); }
    public function bills()           { return $this->hasMany(Bill::class); }

    /* ── Status badge colour ─────────────── */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'available'         => 'bg-green-100 text-green-800',
            'occupied'          => 'bg-blue-100 text-blue-800',
            'under_maintenance' => 'bg-amber-100 text-amber-800',
            default             => 'bg-gray-100 text-gray-800',
        };
    }
}
