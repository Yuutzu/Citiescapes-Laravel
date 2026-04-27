<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $fillable = [
        'sender_name', 'contact_number', 'email',
        'preferred_room_type', 'message', 'status',
        'gm_notes', 'responded_at', 'responded_by',
    ];

    protected function casts(): array
    {
        return ['responded_at' => 'datetime'];
    }

    public function respondedBy() { return $this->belongsTo(User::class, 'responded_by'); }

    public function scopePending($q)    { return $q->where('status', 'pending'); }
    public function scopeResponded($q)  { return $q->where('status', 'responded'); }
    public function scopeClosed($q)     { return $q->where('status', 'closed'); }
}
