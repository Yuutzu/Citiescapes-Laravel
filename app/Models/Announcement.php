<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'body', 'recipient_type', 'recipient_id', 'sent_by', 'email_sent',
    ];

    protected function casts(): array
    {
        return ['email_sent' => 'boolean'];
    }

    public function sentBy()     { return $this->belongsTo(User::class, 'sent_by'); }
    public function recipient()  { return $this->belongsTo(User::class, 'recipient_id'); }

    public function scopeForTenant($q, int $tenantId)
    {
        return $q->where(function ($q) use ($tenantId) {
            $q->where('recipient_type', 'all')
              ->orWhere(fn($q) => $q->where('recipient_type', 'specific')->where('recipient_id', $tenantId));
        });
    }

    public function getRecipientLabelAttribute(): string
    {
        return $this->recipient_type === 'all'
            ? 'All Tenants'
            : ($this->recipient?->full_name ?? 'Specific Tenant');
    }
}
