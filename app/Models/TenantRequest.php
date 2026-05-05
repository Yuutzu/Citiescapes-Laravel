<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantRequest extends Model
{
    protected $fillable = [
        'tenant_id', 'type', 'subject', 'body',
        'status', 'admin_response', 'responded_by', 'responded_at',
    ];

    protected function casts(): array
    {
        return ['responded_at' => 'datetime'];
    }

    public function tenant()      { return $this->belongsTo(User::class, 'tenant_id'); }
    public function respondedBy() { return $this->belongsTo(User::class, 'responded_by'); }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending'     => 'bg-amber-100 text-amber-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'resolved'    => 'bg-green-100 text-green-800',
            default       => 'bg-gray-100 text-gray-800',
        };
    }

    public function getTypeBadgeAttribute(): string
    {
        return $this->type === 'complaint'
            ? 'bg-red-100 text-red-800'
            : 'bg-indigo-100 text-indigo-800';
    }
}
